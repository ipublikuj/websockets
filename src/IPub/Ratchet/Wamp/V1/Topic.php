<?php
/**
 * Topic.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\WAMP\V1;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;

/**
 * A topic/channel containing connections that have subscribed to it
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Topic implements ITopic
{
	/**
	 * If true the TopicManager will destroy this object if it's ever empty of connections
	 *
	 * @type bool
	 */
	private $autoDelete = FALSE;

	/**
	 * @var string
	 */
	private $id;

	/**
	 * @var \SplObjectStorage
	 */
	private $subscribers;

	/**
	 * @param string $topicId Unique ID for this object
	 */
	public function __construct(string $topicId)
	{
		$this->id = $topicId;
		$this->subscribers = new \SplObjectStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId() : string
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString()
	{
		return $this->getId();
	}

	/**
	 * {@inheritdoc}
	 */
	public function broadcast($msg, array $exclude = [], array $eligible = [])
	{
		if (!is_string($msg) && !$msg instanceof Responses\IResponse) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided message for broadcasting have to be string or instance of "%s"', Responses\IResponse::class));
		}

		$useEligible = (bool) count($eligible);

		/** @var Clients\Client $client */
		foreach ($this->subscribers as $client) {
			if (in_array($client->getParameter('wampId'), $exclude)) {
				continue;
			}

			if ($useEligible && !in_array($client->getParameter('wampId'), $eligible)) {
				continue;
			}

			$client->send(Utils\Json::encode([Provider::MSG_EVENT, $this->id, $msg]));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function has(Clients\Client $client) : bool
	{
		return $this->subscribers->contains($client);
	}

	/**
	 * {@inheritdoc}
	 */
	public function add(Clients\Client $client)
	{
		$this->subscribers->attach($client);
	}

	/**
	 * {@inheritdoc}
	 */
	public function remove(Clients\Client $client)
	{
		if ($this->subscribers->contains($client)) {
			$this->subscribers->detach($client);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIterator()
	{
		return $this->subscribers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function count() : int
	{
		return $this->subscribers->count();
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableAutoDelete()
	{
		$this->autoDelete = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disableAutoDelete()
	{
		$this->autoDelete = FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isAutoDeleteEnabled() : bool
	{
		return $this->autoDelete;
	}
}
