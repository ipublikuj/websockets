<?php
/**
 * IDriver.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           23.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Clients\Drivers;

use IPub;
use IPub\WebSockets\Entities;

/**
 * Clients storage driver interface
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IDriver
{
	/**
	 * @param int $id
	 *
	 * @return Entities\Clients\IClient|bool
	 */
	function fetch(int $id);

	/**
	 * @return Entities\Clients\IClient[]
	 */
	function fetchAll() : array;

	/**
	 * @param int $id
	 *
	 * @return bool
	 */
	function contains(int $id) : bool;

	/**
	 * @param int $id
	 * @param mixed $data
	 * @param int $lifeTime
	 *
	 * @return bool True if saved, false otherwise
	 */
	function save(int $id, $data, int $lifeTime = 0) : bool;

	/**
	 * @param int $id
	 *
	 * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise
	 */
	function delete(int $id) : bool;
}
