<?php
/**
 * ClientFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           06.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Clients;

use React\Socket;

use IPub;
use IPub\WebSockets\Entities;

/**
 * Client connection factory
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ClientFactory implements IClientFactory
{
	/**
	 * {@inheritdoc}
	 */
	public function create(int $id, Socket\ConnectionInterface $connection) : Entities\Clients\IClient
	{
		return new Entities\Clients\Client($id, $connection);
	}
}
