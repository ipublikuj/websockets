<?php
/**
 * IClientFactory.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           06.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use React\Socket;

use IPub\WebSockets\Entities;

/**
 * Client connection factory interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IClientFactory
{
	/**
	 * @param int $id
	 * @param Socket\ConnectionInterface $connection
	 *
	 * @return Entities\Clients\IClient
	 */
	public function create(int $id, Socket\ConnectionInterface $connection) : Entities\Clients\IClient;
}
