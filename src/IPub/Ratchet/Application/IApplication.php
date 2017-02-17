<?php
/**
 * IApplication.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           16.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application;

use Ratchet\ConnectionInterface;

/**
 * Ratchet application interface
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IApplication
{
	/**
	 * @param ConnectionInterface $conn
	 *
	 * @return void
	 */
	public function onOpen(ConnectionInterface $conn);

	/**
	 * @param ConnectionInterface $conn
	 *
	 * @return void
	 */
	public function onClose(ConnectionInterface $conn);

	/**
	 * @param ConnectionInterface $conn
	 * @param \Exception $ex
	 *
	 * @return void
	 */
	public function onError(ConnectionInterface $conn, \Exception $ex);
}
