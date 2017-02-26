<?php
/**
 * Provider.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\WAMP\V1;

use Nette;
use Nette\Utils;

use Ratchet\WebSocket;

use Guzzle\Http\Message;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Application\Controller;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Entities;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;
use IPub\Ratchet\Server;
use IPub\Ratchet\WAMP;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Provider extends Application\Application implements WebSocket\WsServerInterface
{
	const MSG_WELCOME = 0;
	const MSG_PREFIX = 1;
	const MSG_CALL = 2;
	const MSG_CALL_RESULT = 3;
	const MSG_CALL_ERROR = 4;
	const MSG_SUBSCRIBE = 5;
	const MSG_UNSUBSCRIBE = 6;
	const MSG_PUBLISH = 7;
	const MSG_EVENT = 8;

	/**
	 * @var \SplObjectStorage
	 */
	private $subscriptions;

	/**
	 * @var Topics\IStorage
	 */
	private $topicsStorage;

	/**
	 * @param Topics\IStorage $topicsStorage
	 * @param Server\OutputPrinter $printer
	 * @param Router\IRouter $router
	 * @param Controller\IControllerFactory $controllerFactory
	 * @param Clients\IStorage $clientsStorage
	 */
	public function __construct(
		WAMP\V1\Topics\IStorage $topicsStorage,
		Server\OutputPrinter $printer,
		Router\IRouter $router,
		Controller\IControllerFactory $controllerFactory,
		Clients\IStorage $clientsStorage
	) {
		parent::__construct($printer, $router, $controllerFactory, $clientsStorage);

		$this->topicsStorage = $topicsStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(Entities\Clients\IClient $client, Message\RequestInterface $request)
	{
		$client->addParameter('wampSession', str_replace('.', '', uniqid((string) mt_rand(), TRUE)));

		// Send welcome handshake
		$client->send(Utils\Json::encode([
			self::MSG_WELCOME,
			$client->getParameter('wampSession'),
			1,
			\Ratchet\VERSION
		]));

		$this->subscriptions = new \SplObjectStorage;

		parent::onOpen($client, $request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Entities\Clients\IClient $client, Message\RequestInterface $request)
	{
		parent::onClose($client, $request);

		foreach ($this->topicsStorage as $topic) {
			$this->cleanTopic($topic, $client);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Entities\Clients\IClient $client, Message\RequestInterface $request, string $message)
	{
		try {
			$json = Utils\Json::decode($message);

			if ($json === NULL || !is_array($json) || $json !== array_values($json)) {
				throw new Exceptions\InvalidArgumentException('Invalid WAMP message format');
			}

			switch ($json[0]) {
				case static::MSG_PREFIX:
					$prefixes = $client->getParameter('prefixes', []);
					$prefixes[$json[1]] = $json[2];

					$client->addParameter('prefixes', $prefixes);

					$client->send(Utils\Json::encode([self::MSG_PREFIX, $json[1], (string) $json[2]]));
					break;

				// RPC action
				case static::MSG_CALL:
					array_shift($json);

					$rpcId = array_shift($json);
					$topic = array_shift($json);

					if (count($json) === 1 && is_array($json[0])) {
						$json = $json[0];
					}

					$request = $this->modifyRequest($request, $this->getTopic($topic), 'call');

					try {
						$response = $this->processMessage($client, $request, [
							'rpcId' => $rpcId,
							'args'  => $json,
						]);

						$client->send(Utils\Json::encode([self::MSG_CALL_RESULT, $rpcId, $response]));

					} catch (\Exception $ex) {
						$data = [self::MSG_CALL_ERROR, $rpcId, $topic, $ex->getMessage(), [
							'code'   => $ex->getCode(),
							'rpc'    => $topic,
							'params' => $json,
						]];

						$client->send(Utils\Json::encode($data));
					}
					break;

				// Subscribe to topic
				case static::MSG_SUBSCRIBE:
					$topic = $this->getTopic($json[1]);

					$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

					if ($subscribedTopics->contains($topic)) {
						return;
					}

					$topic = $this->topicsStorage->getTopic($topic->getId());
					$topic->add($client);

					$this->topicsStorage->addTopic($topic->getId(), $topic);

					$subscribedTopics->attach($topic);

					$client->addParameter('subscribedTopics', $subscribedTopics);

					$request = $this->modifyRequest($request, $topic, 'subscribe');

					$this->processMessage($client, $request, [
						'topic' => $topic,
					]);

					$this->printer->success(sprintf('Connection %s has subscribed to %s', $client->getId(), $topic->getId()));
					break;

				// Unsubscribe from topic
				case static::MSG_UNSUBSCRIBE:
					$topic = $this->getTopic($json[1]);

					$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

					if (!$subscribedTopics->contains($topic)) {
						return;
					}

					$this->cleanTopic($topic, $client);

					$request = $this->modifyRequest($request, $topic, 'unsubscribe');

					$this->processMessage($client, $request, [
						'topic' => $topic,
					]);

					$this->printer->success(sprintf('Connection %s has unsubscribed from %s', $client->getId(), $topic->getId()));
					break;

				// Publish to topic
				case static::MSG_PUBLISH:
					$topic = $this->getTopic($json[1]);

					$exclude = (array_key_exists(3, $json) ? $json[3] : NULL);

					if (!is_array($exclude)) {
						if ((bool) $exclude === TRUE) {
							$exclude = [$client->getParameter('wampSession')];

						} else {
							$exclude = [];
						}
					}

					$eligible = (array_key_exists(4, $json) ? $json[4] : []);

					$event = $json[2];

					$request = $this->modifyRequest($request, $topic, 'publish');

					$this->processMessage($client, $request, [
						'topic'    => $topic,
						'event'    => $event,
						'exclude'  => $exclude,
						'eligible' => $eligible,
					]);

					$this->printer->success(sprintf('Connection %s has published to %s topic', $client->getId(), $topic->getId()));
					break;

				default:
					throw new Exceptions\InvalidArgumentException('Invalid WAMP message type');
			}

		} catch (\Exception $ex) {
			$this->printer->error(sprintf('An error (%s) has occurred: %s', $ex->getCode(), $ex->getMessage()));

			$client->close(1007);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubProtocols()
	{
		return ['wamp'];
	}

	/**
	 * @param string $topic
	 *
	 * @return Entities\Topics\ITopic
	 */
	private function getTopic(string $topic) : Entities\Topics\ITopic
	{
		if (!$this->topicsStorage->hasTopic($topic)) {
			$this->topicsStorage->addTopic($topic, new Entities\Topics\Topic($topic));
		}

		return $this->topicsStorage->getTopic($topic);
	}

	/**
	 * @param Entities\Topics\ITopic $topic
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	private function cleanTopic(Entities\Topics\ITopic $topic, Entities\Clients\IClient $client)
	{
		$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

		if ($subscribedTopics->contains($topic)) {
			$subscribedTopics->detach($topic);
		}

		$topic = $this->topicsStorage->getTopic($topic->getId());
		$topic->remove($client);

		$this->topicsStorage->addTopic($topic->getId(), $topic);

		if ($topic->isAutoDeleteEnabled() && $topic->count() === 0) {
			$this->topicsStorage->removeTopic($topic->getId());
		}
	}

	/**
	 * @param Message\RequestInterface $request
	 * @param Entities\Topics\ITopic $topic
	 * @param string $action
	 *
	 * @return Message\RequestInterface
	 */
	private function modifyRequest(Message\RequestInterface $request, Entities\Topics\ITopic $topic, string $action) : Message\RequestInterface
	{
		$url = $request->getUrl(TRUE);
		$url->setPath(rtrim($url->getPath(), '/') . '/' . ltrim($topic->getId(), '/'));

		$query = $url->getQuery();
		$query->add('action', $action);

		$url->setQuery($query);

		$request->setUrl($url);

		return $request;
	}
}
