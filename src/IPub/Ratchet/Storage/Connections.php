<?php
/**
 * Connections.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Storage
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Storage;

use Doctrine\Common\Cache\CacheProvider;
use Nette;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Server;

/**
 * Storage for manage all connections
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Storage
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @author         Vít Ledvinka, frosty22 <ledvinka.vit@gmail.com>
 *
 * @method onOpen(Server\Connection $client)
 * @method onClose(Server\Connection $client)
 */
final class Connections implements \IteratorAggregate
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var \Closure
	 */
	public $onOpen = [];

	/**
	 * @var \Closure
	 */
	public $onClose = [];

	/**
	 * @var \SplObjectStorage
	 */
	private $clients;

	public function __construct(CacheProvider $cacheProvider)
	{
		$this->clients = new \SplObjectStorage;
	}

	/**
	 * @param ConnectionInterface $client
	 *
	 * @return void
	 */
	public function addClient(ConnectionInterface $client)
	{
		if (!$this->clients->contains($client)) {
			$this->clients->attach($client);

			$clientProxy = new Server\Connection($client);

			$this->onOpen($clientProxy);
		}
	}

	/**
	 * @param ConnectionInterface $client
	 *
	 * @return void
	 */
	public function removeClient(ConnectionInterface $client)
	{
		if ($this->clients->contains($client)) {
			$clientProxy = new Server\Connection($client);

			$this->onClose($clientProxy);

			$this->clients->detach($client);
		}
	}

	/**
	 * @return Server\Connection[]
	 */
	public function getIterator()
	{
		$clients = new \SplObjectStorage;

		foreach ($this->clients as $client)
		{
			$clients->attach(new Server\Connection($client));
		}

		return $clients;
	}

	/**
	 * Send response to the all connections
	 *
	 * @param Responses\IResponse $response
	 */
	public function sendAll(Responses\IResponse $response)
	{
		/** @var ConnectionInterface $client */
		foreach ($this->clients as $client) {
			$client->send($response);
		}
	}
}
