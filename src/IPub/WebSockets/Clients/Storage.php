<?php
/**
 * Storage.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Storage
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use ArrayIterator;
use Throwable;

use Nette;

use Psr\Log;

use IPub\WebSockets\Clients\Drivers;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;

/**
 * Storage for manage all connections
 *
 * @package        iPublikuj:WebSockets!
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
	public function __construct(int $ttl = 0, ?Log\LoggerInterface $logger = NULL)
	{
		$this->ttl = $ttl;
		$this->logger = $logger === NULL ? new Log\NullLogger : $logger;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setStorageDriver(Drivers\IDriver $driver) : void
	{
		$this->driver = $driver;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\ClientNotFoundException
	 * @throws Exceptions\StorageException
	 */
	public function getClient(int $identifier) : Entities\Clients\IClient
	{
		try {
			$result = $this->driver->fetch($identifier);

		} catch (Throwable $ex) {
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
	 *
	 * @throws Exceptions\StorageException
	 */
	public function addClient(int $identifier, Entities\Clients\IClient $client) : void
	{
		$context = [
			'user' => $client->getUser(),
		];

		if ($client->getUser() instanceof Nette\Security\User) {
			$context['userId'] = $client->getUser()->getId();
		}

		$this->logger->debug(sprintf('INSERT CLIENT ' . $identifier), $context);

		try {
			$result = $this->driver->save($identifier, $client, $this->ttl);

		} catch (Throwable $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		if ($result === FALSE) {
			throw new Exceptions\StorageException('Unable add client');
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function hasClient(int $identifier) : bool
	{
		try {
			$result = $this->driver->contains($identifier);

		} catch (Throwable $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function removeClient(int $identifier) : bool
	{
		$this->logger->debug('REMOVE CLIENT ' . $identifier);

		try {
			$result = $this->driver->delete($identifier);

		} catch (Throwable $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', get_class($this)), $ex->getCode(), $ex);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function refreshClient(Entities\Clients\IClient $client) : void
	{
		if ($this->hasClient($client->getId())) {
			$this->driver->save($client->getId(), $client, $this->ttl);

			$this->logger->debug(sprintf('REFRESH CLIENT ' . $client->getId()));
		}
	}

	/**
	 * @return Entities\Clients\IClient[]|ArrayIterator
	 */
	public function getIterator() : ArrayIterator
	{
		return new ArrayIterator($this->driver->fetchAll());
	}

}
