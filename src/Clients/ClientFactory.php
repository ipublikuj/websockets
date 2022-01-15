<?php declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use IPub\WebSockets\Entities;
use React\Socket;

/**
 * Client connection factory
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ClientFactory implements IClientFactory
{

	/**
	 * {@inheritdoc}
	 */
	public function create(int $id, Socket\ConnectionInterface $connection): Entities\Clients\IClient
	{
		return new Entities\Clients\Client($id, $connection);
	}

}
