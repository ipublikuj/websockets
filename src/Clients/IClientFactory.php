<?php declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use IPub\WebSockets\Entities;
use React\Socket;

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
	public function create(int $id, Socket\ConnectionInterface $connection): Entities\Clients\IClient;

}
