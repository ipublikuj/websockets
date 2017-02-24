<?php
/**
 * Storage.php
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

namespace IPub\Ratchet\Clients;

use Nette;

use Psr\Log;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Clients\Drivers;
use IPub\Ratchet\Exceptions;
use Ratchet\Server\IoConnection;

/**
 * Storage for manage all connections
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Storage
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Storage implements IStorage
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Drivers\IDriver
	 */
	private $driver;

	/**
	 * @var int|NULL
	 */
	private $ttl;

	/**
	 * @var Log\LoggerInterface|Log\NullLogger|NULL
	 */
	private $logger;

	/**
	 * @param int|NULL $ttl
	 * @param Log\LoggerInterface|NULL $logger
	 */
	public function __construct(int $ttl = 0, Log\LoggerInterface $logger = NULL)
	{
		$this->ttl = $ttl;
		$this->logger = $logger === NULL ? new Log\NullLogger : $logger;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setStorageDriver(Drivers\IDriver $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getStorageId(ConnectionInterface $connection) : int
	{
		if ($connection instanceof IoConnection) {
			return $connection->resourceId;
		}

		throw new Exceptions\InvalidStateException('Provided connection is not instance of \Ratchet\Server\IoConnection');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClient(int $identifier) : Client
	{
		try {
			$result = $this->driver->fetch($identifier);

		} catch (\Exception $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		$this->logger->debug('GET CLIENT ' . $identifier);

		if ($result === FALSE) {
			throw new Exceptions\ClientNotFoundException(sprintf('Client %s not found', $identifier));
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addClient(int $identifier, ConnectionInterface $connection)
	{
		$client = new Client($connection);

		$serializedClient = $client;

		$context = [
			'user' => $client->getUser(),
		];

		if ($client->getUser() instanceof Nette\Security\User) {
			$context['userId'] = $client->getUser()->getId();
		}

		$this->logger->debug(sprintf('INSERT CLIENT ' . $identifier), $context);

		try {
			$result = $this->driver->save($identifier, $serializedClient, $this->ttl);

		} catch (\Exception $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		if ($result === FALSE) {
			throw new Exceptions\StorageException('Unable add client');
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasClient(int $identifier) : bool
	{
		try {
			$result = $this->driver->contains($identifier);

		} catch (\Exception $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 */
	public function removeClient(int $identifier) : bool
	{
		$this->logger->debug('REMOVE CLIENT ' . $identifier);

		try {
			$result = $this->driver->delete($identifier);

		} catch (\Exception $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		return $result;
	}

	/**
	 * @return Client[]|\ArrayIterator
	 */
	public function getIterator() : \ArrayIterator
	{
		return new \ArrayIterator($this->driver->fetchAll());
	}
}
