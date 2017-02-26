<?php
/**
 * IDriver.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 * @since          1.0.0
 *
 * @date           26.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\WAMP\V1\Topics\Drivers;

use IPub;
use IPub\Ratchet\Entities;
use IPub\Ratchet\WAMP;

/**
 * Topics storage driver interface
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IDriver
{
	/**
	 * @param string $id
	 *
	 * @return Entities\Topics\ITopic|bool
	 */
	function fetch(string $id);

	/**
	 * @return Entities\Topics\ITopic[]
	 */
	function fetchAll() : array;

	/**
	 * @param string $id
	 *
	 * @return bool
	 */
	function contains(string $id) : bool;

	/**
	 * @param string $id
	 * @param mixed $data
	 * @param int $lifeTime
	 *
	 * @return bool True if saved, false otherwise
	 */
	function save(string $id, $data, int $lifeTime = 0) : bool;

	/**
	 * @param string $id
	 *
	 * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise
	 */
	function delete(string $id) : bool;
}
