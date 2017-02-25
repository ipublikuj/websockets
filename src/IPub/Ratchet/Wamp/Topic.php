<?php
/**
 * Topic.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Wamp
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Wamp;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Clients;

/**
 * A topic/channel containing connections that have subscribed to it
 */
class Topic implements \IteratorAggregate, \Countable
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
     * @return string
     */
    public function getId() : string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getId();
    }

    /**
     * Send a message to all the connections in this topic
     *
     * @param string $msg     Payload to publish
     * @param array $exclude  A list of session IDs the message should be excluded from (blacklist)
     * @param array $eligible A list of session Ids the message should be send to (whitelist)
     *
     * @return void
     */
    public function broadcast($msg, array $exclude = [], array $eligible = [])
    {
        $useEligible = (bool) count($eligible);

        /** @var Clients\Client $client */
        foreach ($this->subscribers as $client) {
            if (in_array($client->getParameter('wampId'), $exclude)) {
                continue;
            }

            if ($useEligible && !in_array($client->getParameter('wampId'), $eligible)) {
                continue;
            }

            $client->send(Utils\Json::encode([Application\WampApplication::MSG_EVENT, $this->id, $msg]));
        }
    }

    /**
     * @param  Clients\Client $client
     *
     * @return bool
     */
    public function has(Clients\Client $client) : bool
    {
        return $this->subscribers->contains($client);
    }

    /**
     * @param Clients\Client $client
     *
     * @return void
     */
    public function add(Clients\Client $client)
    {
        $this->subscribers->attach($client);
    }

    /**
     * @param Clients\Client $client
     *
     * @return void
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
     * @return void
     */
    public function enableAutoDelete()
    {
        $this->autoDelete = TRUE;
    }

    /**
     * @return void
     */
    public function disableAutoDelete()
    {
        $this->autoDelete = FALSE;
    }

    /**
     * @return bool
     */
    public function isAutoDeleteEnabled() : bool
    {
        return $this->autoDelete;
    }
}
