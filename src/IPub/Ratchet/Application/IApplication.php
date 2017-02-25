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

use IPub;
use IPub\Ratchet\Clients;

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
	 * When a new connection is opened it will be passed to this method
	 *
	 * @param Clients\IClient $client
	 *
	 * @return mixed
	 */
	function onOpen(Clients\IClient $client);

	/**
	 * This is called before or after a socket is closed (depends on how it's closed)
	 * SendMessage to $client will not result in an error if it has already been closed
	 *
	 * @param Clients\IClient $client
	 *
	 * @return mixed
	 */
	function onClose(Clients\IClient $client);

	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
	 *
	 * @param Clients\IClient $client
	 * @param \Exception $ex
	 *
	 * @return mixed
	 */
	function onError(Clients\IClient $client, \Exception $ex);

	/**
	 * Triggered when a client sends data through the socket
	 *
	 * @param Clients\IClient $from
	 * @param string $message
	 *
	 * @return mixed
	 */
	function onMessage(Clients\IClient $from, string $message);
}
