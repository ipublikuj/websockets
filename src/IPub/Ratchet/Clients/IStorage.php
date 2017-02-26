<?php
/**
 * IStorage.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           24.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Clients;

use Nette;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Clients\Drivers;
use IPub\Ratchet\Entities;

/**
 * Storage for manage all connections
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IStorage extends \IteratorAggregate
{
	/**
	 * @param Drivers\IDriver $driver
	 *
	 * @return void
	 */
	function setStorageDriver(Drivers\IDriver $driver);

	/**
	 * @param ConnectionInterface $connection
	 *
	 * @return int
	 */
	static function getStorageId(ConnectionInterface $connection) : int;

	/**
	 * @param int $identifier
	 *
	 * @return Entities\Clients\IClient
	 */
	function getClient(int $identifier) : Entities\Clients\IClient;

	/**
	 * @param int $identifier
	 * @param ConnectionInterface $connection
	 *
	 * @return void
	 */
	function addClient(int $identifier, ConnectionInterface $connection);

	/**
	 * @param int $identifier
	 *
	 * @return bool
	 */
	function hasClient(int $identifier) : bool;

	/**
	 * @param int $identifier
	 *
	 * @return bool
	 */
	function removeClient(int $identifier) : bool;
}
