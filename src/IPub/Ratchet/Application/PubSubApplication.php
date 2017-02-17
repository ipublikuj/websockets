<?php
/**
 * PubSubApplication.php
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

use Ratchet\ConnectionInterface;
use Ratchet\Wamp;

use IPub;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Storage;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class PubSubApplication extends Application implements Wamp\WampServerInterface
{
	/**
	 * A lookup of all the topics clients have subscribed to
	 *
	 * @var Wamp\Topic[]
	 */
	private $subscribedTopics = [];

	/**
	 * {@inheritdoc}
	 */
	public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
	{

	}

	/**
	 * {@inheritdoc}
	 */
	public function onSubscribe(ConnectionInterface $conn, $topic)
	{
		$this->subscribedTopics[(string) $topic] = $topic;

		echo "Connection {$conn->resourceId} has been subscribed to {$topic->getId()}\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onUnSubscribe(ConnectionInterface $conn, $topic)
	{
		echo "Connection {$conn->resourceId} has been unsubscribed form {$topic->getId()}\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
	{

	}
}
