<?php declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use ArrayIterator;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use Nette;
use Psr\Log;
use Throwable;

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

	/** @var Drivers\IDriver */
	private $driver;

	/** @var int|null */
	private $ttl;

	/** @var Log\LoggerInterface|Log\NullLogger|null */
	private $logger;

	/**
	 * @param int|null $ttl
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(int $ttl = 0, ?Log\LoggerInterface $logger = null)
	{
		$this->ttl = $ttl;
		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * {@inheritdoc}
	 */
	public function setStorageDriver(Drivers\IDriver $driver): void
	{
		$this->driver = $driver;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\ClientNotFoundException
	 * @throws Exceptions\StorageException
	 */
	public function getClient(int $identifier): Entities\Clients\IClient
	{
		try {
			$result = $this->driver->fetch($identifier);

		} catch (Throwable $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', self::class), $ex->getCode(), $ex);
		}

		$this->logger->debug('GET CLIENT ' . $identifier);

		if ($result === false) {
			throw new Exceptions\ClientNotFoundException(sprintf('Client %s not found', $identifier));
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function addClient(int $identifier, Entities\Clients\IClient $client): void
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
			throw new Exceptions\StorageException(sprintf('Driver %s failed', self::class), $ex->getCode(), $ex);
		}

		if ($result === false) {
			throw new Exceptions\StorageException('Unable add client');
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function hasClient(int $identifier): bool
	{
		try {
			$result = $this->driver->contains($identifier);

		} catch (Throwable $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', self::class), $ex->getCode(), $ex);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function removeClient(int $identifier): bool
	{
		$this->logger->debug('REMOVE CLIENT ' . $identifier);

		try {
			$result = $this->driver->delete($identifier);

		} catch (Throwable $ex) {
			throw new Exceptions\StorageException(sprintf('Driver %s failed', self::class), $ex->getCode(), $ex);
		}

		return $result;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\StorageException
	 */
	public function refreshClient(Entities\Clients\IClient $client): void
	{
		if ($this->hasClient($client->getId())) {
			$this->driver->save($client->getId(), $client, $this->ttl);

			$this->logger->debug(sprintf('REFRESH CLIENT ' . $client->getId()));
		}
	}

	/**
	 * @return Entities\Clients\IClient[]|ArrayIterator
	 */
	public function getIterator(): ArrayIterator
	{
		return new ArrayIterator($this->driver->fetchAll());
	}

}
