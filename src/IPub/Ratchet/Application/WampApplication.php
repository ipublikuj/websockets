<?php
/**
 * WampApplication.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application;

use Nette;
use Nette\Utils;

use Ratchet\WebSocket;

use Guzzle\Http\Message;

use IPub;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Wamp;


/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class WampApplication extends Application implements WebSocket\WsServerInterface
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
	public function onOpen(Clients\Client $client)
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
	public function onMessage(Clients\Client $client, $msg)
	{
		try {
			$json = Utils\Json::decode($msg);

			if ($json === NULL || !is_array($json) || $json !== array_values($json)) {
				throw new Exceptions\InvalidArgumentException('Invalid WAMP message format');
			}

			switch ($json[0]) {
				case static::MSG_PREFIX:
					$prefixes = $client->getParameter('prefixes', []);
					$prefixes[$json[1]] = $json[2];

					$client->addParameter('prefixes', $prefixes);
					break;

				case static::MSG_CALL:
					array_shift($json);
					$callID = array_shift($json);
					$topic = array_shift($json);

					if (count($json) == 1 && is_array($json[0])) {
						$json = $json[0];
					}

					$client = $this->modifyRequest($client, $this->getTopic($topic), 'call');

					parent::onMessage($client, [
						'rpcId' => $callID,
						'args' => $json,
					]);
					break;

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

					parent::onMessage($client, [
						'topic' => $topic,
					]);
					break;

				case static::MSG_UNSUBSCRIBE:
					$topic = $this->getTopic($json[1]);

					$subscribedTopics = $client->getParameter('subscribedTopics', new \SplObjectStorage());

					if (!$subscribedTopics->contains($topic)) {
						return;
					}

					$this->cleanTopic($topic, $client);

					echo "Connection {$client->getId()} has unsubscribed from {$topic->getId()}\n";

					$client = $this->modifyRequest($client, $topic, 'unsubscribe');

					parent::onMessage($client, [
						'topic' => $topic,
					]);
					break;

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

					parent::onMessage($client, [
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
	public function onClose(Clients\Client $client)
	{
		parent::onClose($client);

		foreach ($this->topicLookup as $topic) {
			$this->cleanTopic($topic, $client);
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
	 * @return Wamp\Topic
	 */
	private function getTopic(string $topic) : Wamp\Topic
	{
		if (!array_key_exists($topic, $this->topicLookup)) {
			$this->topicLookup[$topic] = new Wamp\Topic($topic);
		}

		return $this->topicLookup[$topic];
	}

	/**
	 * @param Wamp\Topic $topic
	 * @param Clients\Client $client
	 *
	 * @return void
	 */
	private function cleanTopic(Wamp\Topic $topic, Clients\Client $client)
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
	 * @param Clients\Client $client
	 * @param Wamp\Topic $topic
	 * @param string $action
	 *
	 * @return Clients\Client
	 */
	private function modifyRequest(Clients\Client $client, Wamp\Topic $topic, string $action) : Clients\Client
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
