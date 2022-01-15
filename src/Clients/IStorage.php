<?php declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use IPub\WebSockets\Entities;
use IteratorAggregate;

/**
 * Storage for manage all connections
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IStorage extends IteratorAggregate
{

	/**
	 * @param Drivers\IDriver $driver
	 *
	 * @return void
	 */
	public function setStorageDriver(Drivers\IDriver $driver): void;

	/**
	 * @param int $identifier
	 *
	 * @return Entities\Clients\IClient
	 */
	public function getClient(int $identifier): Entities\Clients\IClient;

	/**
	 * @param int $identifier
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	public function addClient(int $identifier, Entities\Clients\IClient $client): void;

	/**
	 * @param int $identifier
	 *
	 * @return bool
	 */
	public function hasClient(int $identifier): bool;

	/**
	 * @param int $identifier
	 *
	 * @return bool
	 */
	public function removeClient(int $identifier): bool;

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	public function refreshClient(Entities\Clients\IClient $client): void;

}
