<?php
/**
 * Wrapper.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use Nette;

use IPub;
use IPub\WebSockets\Application;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;
use IPub\WebSockets\Protocols;

/**
 * WebSockets server application wrapper
 * Purpose of this class is to create better interface for connection objects
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onClientConnected(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
 * @method onClientDisconnected(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
 * @method onClientError(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
 * @method onIncomingMessage(Entities\Clients\IClient $client, Http\IRequest $httpRequest, $message)
 * @method onAfterIncomingMessage(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
 */
final class Wrapper implements IWrapper
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var \Closure
	 */
	public $onClientConnected = [];

	/**
	 * @var \Closure
	 */
	public $onClientDisconnected = [];

	/**
	 * @var \Closure
	 */
	public $onClientError = [];

	/**
	 * @var \Closure
	 */
	public $onIncomingMessage = [];

	/**
	 * @var \Closure
	 */
	public $onAfterIncomingMessage = [];

	/**
	 * @var Application\IApplication
	 */
	private $application;

	/**
	 * @var Clients\IStorage
	 */
	private $clientsStorage;

	/**
	 * Flag if we have checked the decorated application for sub-protocols
	 *
	 * @var boolean
	 */
	private $isSpGenerated = FALSE;

	/**
	 * Holder of accepted protocols
	 *
	 * @var array
	 */
	private $acceptedSubProtocols = [];

	/**
	 * @var Protocols\ProtocolProxy
	 */
	private $protocolsProxy;

	/**
	 * @var Http\RequestFactory
	 */
	private $requestFactory;

	/**
	 * @param Application\IApplication $application
	 * @param Clients\IStorage $clientsStorage
	 */
	public function __construct(
		Application\IApplication $application,
		Clients\IStorage $clientsStorage
	) {
		$this->application = $application;
		$this->clientsStorage = $clientsStorage;

		$this->protocolsProxy = new Protocols\ProtocolProxy();
		$this->protocolsProxy->enableProtocol(new Protocols\RFC6455());
		$this->protocolsProxy->enableProtocol(new Protocols\HyBi10());

		$this->requestFactory = new Http\RequestFactory();
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(Entities\Clients\IClient $client)
	{
		$client->setHTTPHeadersReceived(FALSE);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Entities\Clients\IClient $client, string $message)
	{
		if (!$client->isHTTPHeadersReceived()) {
			$client->setHttpBuffer($client->getHttpBuffer() . $message);

			try {
				if (($httpRequest = $this->requestFactory->createHttpRequest($client->getHttpBuffer())) === NULL) {
					return;
				}

				$client->setRequest($httpRequest);
				$client->setHttpBuffer('');

			} catch (\OverflowException $ex) {
				$this->close($client, Http\IResponse::S413_REQUEST_ENTITY_TOO_LARGE);

				return;
			}

			$client->setHTTPHeadersReceived(TRUE);

			$this->connectionOpen($client, $httpRequest);

			return;
		}

		$this->connectionMessage($client, $message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Entities\Clients\IClient $client)
	{
		if ($client->isHTTPHeadersReceived()) {
			$this->connectionClose($client);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(Entities\Clients\IClient $client, \Exception $ex)
	{
		if ($client->isHTTPHeadersReceived()) {
			$this->connectionError($client, $ex);

		} else {
			$this->close($client, Http\IResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}


	/**
	 * @param Entities\Clients\IClient $client
	 * @param Http\IRequest $httpRequest
	 *
	 * @return void
	 */
	private function connectionOpen(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
	{
		if (!$this->protocolsProxy->isProtocolEnabled($httpRequest)) {
			$this->close($client);

			return;
		}

		try {
			$protocol = $this->protocolsProxy->getProtocol($httpRequest);

			$webSocket = new Entities\WebSockets\WebSocket(FALSE, FALSE, $protocol);

			$client->setWebSocket($webSocket);

			$this->attemptUpgrade($client);

		} catch (Exceptions\ClientNotFoundException $ex) {
			$this->close($client, Http\IResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	private function connectionClose(Entities\Clients\IClient $client)
	{
		try {
			$this->clientsStorage->removeClient($client->getId());

			// Call service event
			$this->onClientDisconnected($client, $client->getRequest());

			// Call application event
			$this->application->onClose($client, $client->getRequest());

		} catch (Exceptions\ClientNotFoundException $ex) {
			$this->close($client, Http\IResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @param Entities\Clients\IClient $client
	 * @param \Exception $ex
	 *
	 * @return void
	 */
	public function connectionError(Entities\Clients\IClient $client, \Exception $ex)
	{
		try {
			$webSocket = $client->getWebSocket();

			if ($webSocket->isEstablished()) {
				// Call service event
				$this->onClientError($client, $client->getRequest());

				// Call application event
				$this->application->onError($client, $client->getRequest(), $ex);

				return;
			}

			$client->getConnection()->end();

		} catch (Exceptions\ClientNotFoundException $ex) {
			$this->close($client, Http\IResponse::S500_INTERNAL_SERVER_ERROR);
		}
	}

	/**
	 * @param Entities\Clients\IClient $client
	 * @param $message
	 *
	 * @return void
	 */
	private function connectionMessage(Entities\Clients\IClient $client, $message)
	{
		$webSocket = $client->getWebSocket();

		if ($webSocket->isClosing()) {
			return;
		}

		if ($webSocket->isEstablished() === TRUE) {
			// Call service event
			$this->onIncomingMessage($client, $client->getRequest(), $message);

			$webSocket->getProtocol()->handleMessage($client, $this->application, $message);

			// Call service event
			$this->onAfterIncomingMessage($client, $client->getRequest());

			return;
		}

		$this->attemptUpgrade($client, $message);
	}

	/**
	 * @param Entities\Clients\IClient $client
	 * @param string $data
	 *
	 * @return mixed
	 */
	private function attemptUpgrade(
		Entities\Clients\IClient $client,
		$data = ''
	) {
		/** @var Http\IRequest $httpRequest */
		$httpRequest = $client->getRequest();

		$webSocket = $client->getWebSocket();

		try {
			$response = $webSocket->getProtocol()->doHandshake($httpRequest);

		} catch (\UnderflowException $e) {
			return NULL;
		}

		if (($subHeader = $httpRequest->getHeader('Sec-WebSocket-Protocol')) !== NULL) {
			$values = [];

			if (strpos($subHeader, ',') !== FALSE) {
				// Explode on glue when the glue is not inside of a comma
				foreach (preg_split('/' . preg_quote(',') . '(?=([^"]*"[^"]*")*[^"]*$)/', $subHeader) as $v) {
					$values[] = strtolower(trim($v));
				}

			} elseif (trim($subHeader) !== '') {
				$values[] = strtolower(trim($subHeader));
			}

			if (($agreedSubProtocols = $this->getSubProtocolString($values)) !== '') {
				$response->addHeader('Sec-WebSocket-Protocol', $agreedSubProtocols);
			}
		}

		$response->addHeader('X-Powered-By', Server::VERSION);

		$client->getConnection()->write((string) $response);

		if ($response->getCode() !== Http\IResponse::S101_SWITCHING_PROTOCOLS) {
			$client->getConnection()->end();

			return NULL;
		}

		$webSocket->setEstablished(TRUE);

		// Call service event
		$this->onClientConnected($client, $httpRequest);

		// Call application event
		return $this->application->onOpen($client, $httpRequest);
	}

	/**
	 * @param array $httpRequested
	 *
	 * @return string
	 */
	private function getSubProtocolString(array $httpRequested = []) : string
	{
		if ($httpRequested !== []) {
			foreach ($httpRequested as $sub) {
				if ($this->isSubProtocolSupported($sub)) {
					return $sub;
				}
			}
		}

		return '';
	}

	/**
	 * @param string
	 *
	 * @return boolean
	 */
	private function isSubProtocolSupported(string $name) : bool
	{
		if (!$this->isSpGenerated) {
			$this->acceptedSubProtocols = array_flip($this->application->getSubProtocols());

			$this->isSpGenerated = TRUE;
		}

		return array_key_exists($name, $this->acceptedSubProtocols);
	}

	/**
	 * Close a connection with an HTTP response
	 *
	 * @param Entities\Clients\IClient $client
	 * @param int $code HTTP status code
	 * @param mixed $body
	 *
	 * @return void
	 */
	private function close(Entities\Clients\IClient $client, $code = 400, $body = NULL)
	{
		$response = new Http\Response($code, [
			'Sec-WebSocket-Version' => $this->protocolsProxy->getSupportedProtocols(),
			'X-Powered-By'          => Server::VERSION,
		], $body);

		$client->getConnection()->write((string) $response);
		$client->getConnection()->end();
	}
}
