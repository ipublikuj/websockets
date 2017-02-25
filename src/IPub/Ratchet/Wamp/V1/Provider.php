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
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;

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
	 * @var array
	 */
	private $topicLookup = [];

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(Clients\IClient $client)
	{
		$client->addParameter('wampId', str_replace('.', '', uniqid((string) mt_rand(), TRUE)));

		// Send welcome handshake
		$client->send(Utils\Json::encode([
			self::MSG_WELCOME,
			$client->getParameter('wampId'),
			1,
			\Ratchet\VERSION
		]));

		$this->subscriptions = new \SplObjectStorage;

		parent::onOpen($client);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Clients\IClient $client)
	{
		parent::onClose($client);

		foreach ($this->topicLookup as $topic) {
			$this->cleanTopic($topic, $client);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Clients\IClient $client, string $message)
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

					$client = $this->modifyRequest($client, $this->getTopic($topic), 'call');

					try {
						$response = $this->processMessage($client, [
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

					$this->topicLookup[$topic->getId()]->add($client);

					$subscribedTopics->attach($topic);

					$client->addParameter('subscribedTopics', $subscribedTopics);

					echo "Connection {$client->getId()} has subscribed to {$topic->getId()}\n";

					$client = $this->modifyRequest($client, $topic, 'subscribe');

					$this->processMessage($client, [
						'topic' => $topic,
					]);
					break;

				// Unsubscribe from topic
				case static::MSG_UNSUBSCRIBE:
					$topic = $this->getTopic($json[1]);

					$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

					if (!$subscribedTopics->contains($topic)) {
						return;
					}

					$this->cleanTopic($topic, $client);

					echo "Connection {$client->getId()} has unsubscribed from {$topic->getId()}\n";

					$client = $this->modifyRequest($client, $topic, 'unsubscribe');

					$this->processMessage($client, [
						'topic' => $topic,
					]);
					break;

				// Publish to topic
				case static::MSG_PUBLISH:
					$topic = $this->getTopic($json[1]);

					$exclude = (array_key_exists(3, $json) ? $json[3] : NULL);

					if (!is_array($exclude)) {
						if ((bool) $exclude === TRUE) {
							$exclude = [$client->getParameter('wampId')];

						} else {
							$exclude = [];
						}
					}

					$eligible = (array_key_exists(4, $json) ? $json[4] : []);

					$event = $json[2];

					$client = $this->modifyRequest($client, $topic, 'publish');

					$this->processMessage($client, [
						'topic'    => $topic,
						'event'    => $event,
						'exclude'  => $exclude,
						'eligible' => $eligible,
					]);
					break;

				default:
					throw new Exceptions\InvalidArgumentException('Invalid WAMP message type');
			}

		} catch (\Exception $ex) {
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
	 * @return ITopic
	 */
	private function getTopic(string $topic) : ITopic
	{
		if (!array_key_exists($topic, $this->topicLookup)) {
			$this->topicLookup[$topic] = new Topic($topic);
		}

		return $this->topicLookup[$topic];
	}

	/**
	 * @param ITopic $topic
	 * @param Clients\IClient $client
	 *
	 * @return void
	 */
	private function cleanTopic(ITopic $topic, Clients\IClient $client)
	{
		$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

		if ($subscribedTopics->contains($topic)) {
			$subscribedTopics->detach($topic);
		}

		$this->topicLookup[$topic->getId()]->remove($client);

		if ($topic->isAutoDeleteEnabled() && $topic->count() === 0) {
			unset($this->topicLookup[$topic->getId()]);
		}
	}

	/**
	 * @param Clients\IClient $client
	 * @param ITopic $topic
	 * @param string $action
	 *
	 * @return Clients\IClient
	 */
	private function modifyRequest(Clients\IClient $client, ITopic $topic, string $action) : Clients\IClient
	{
		$request = $client->getRequest();

		$url = $request->getUrl(TRUE);
		$url->setPath(rtrim($url->getPath(), '/') . '/' . ltrim($topic->getId(), '/'));

		$query = $url->getQuery();
		$query->add('action', $action);

		$url->setQuery($query);

		$request->setUrl($url);

		$client->setRequest($request);

		return $client;
	}
}
