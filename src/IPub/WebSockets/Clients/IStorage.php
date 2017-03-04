<?php
/**
 * IStorage.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           24.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use IPub;
use IPub\WebSockets\Clients\Drivers;
use IPub\WebSockets\Entities;

/**
 * Storage for manage all connections
 *
 * @package        iPublikuj:WebSocket!
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
	 * @param int $identifier
	 *
	 * @return Entities\Clients\IClient
	 */
	function getClient(int $identifier) : Entities\Clients\IClient;

	/**
	 * @param int $identifier
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	function addClient(int $identifier, Entities\Clients\IClient $client);

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

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	function refreshClient(Entities\Clients\IClient $client);
}
