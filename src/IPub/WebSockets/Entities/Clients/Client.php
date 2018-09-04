<?php
/**
 * Client.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Entities\Clients;

use Nette;
use Nette\Security as NS;
use Nette\Utils;

use React\Socket;

use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;
use IPub\WebSockets\Protocols;

/**
 * Single client connection
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Client implements IClient
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Socket\ConnectionInterface
	 */
	private $connection;

	/**
	 * @var NS\User|NULL
	 */
	private $user;

	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var bool
	 */
	private $httpHeadersReceived = FALSE;

	/**
	 * @var string
	 */
	private $httpBuffer = '';

	/**
	 * @var string|NULL
	 */
	private $remoteAddress;

	/**
	 * @var Http\IRequest
	 */
	private $httpRequest;

	/**
	 * @var Entities\WebSockets\IWebSocket
	 */
	private $webSocket;

	/**
	 * @var Utils\ArrayHash
	 */
	private $parameters;

	/**
	 * @param int $id
	 * @param Socket\ConnectionInterface $connection
	 */
	public function __construct(int $id, Socket\ConnectionInterface $connection)
	{
		$this->connection = $connection;

		$this->id = $id;
		$this->remoteAddress = $connection->getRemoteAddress();

		$this->parameters = new Utils\ArrayHash;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId() : int
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConnection() : Socket\ConnectionInterface
	{
		return $this->connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHTTPHeadersReceived(bool $state) : void
	{
		$this->httpHeadersReceived = $state;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHTTPHeadersReceived() : bool
	{
		return $this->httpHeadersReceived;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHttpBuffer(string $buffer) : void
	{
		$this->httpBuffer = $buffer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHttpBuffer() : string
	{
		return $this->httpBuffer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRequest(Http\IRequest $httpRequest) : void
	{
		$this->httpRequest = $httpRequest;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRequest() : Http\IRequest
	{
		return clone $this->httpRequest;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setWebSocket(Entities\WebSockets\IWebSocket $webSocket) : void
	{
		$this->webSocket = $webSocket;
	}

	/**
	 * @return Entities\WebSockets\IWebSocket
	 */
	public function getWebSocket() : Entities\WebSockets\IWebSocket
	{
		return $this->webSocket;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addParameter(string $key, $value) : void
	{
		$this->parameters->offsetSet($key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameter(string $key, $default = NULL)
	{
		return $this->parameters->offsetExists($key) ? $this->parameters->offsetGet($key) : $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close(?int $code = NULL) : void
	{
		$this->webSocket->getProtocol()->close($this, $code);
	}

	/**
	 * {@inheritdoc}
	 */
	public function send($response) : void
	{
		if ($response instanceof Responses\IResponse) {
			$response = (string) $response;

		} elseif (!$response instanceof Protocols\IData) {
			$response = (string) $response;
		}

		$this->webSocket->getProtocol()->send($this, $response);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setUser(NS\User $user) : void
	{
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUser() : ?NS\User
	{
		return $this->user;
	}
}
