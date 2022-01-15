<?php declare(strict_types = 1);

namespace IPub\WebSockets\Application;

use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;
use Throwable;

/**
 * WebSockets application interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IApplication
{

	/**
	 * When a new connection is opened it will be passed to this method
	 *
	 * @param Entities\Clients\IClient $client
	 * @param Http\IRequest $httpRequest
	 *
	 * @return void
	 */
	public function handleOpen(Entities\Clients\IClient $client, Http\IRequest $httpRequest): void;

	/**
	 * This is called before or after a socket is closed (depends on how it's closed)
	 * SendMessage to $client will not result in an error if it has already been closed
	 *
	 * @param Entities\Clients\IClient $client
	 * @param Http\IRequest $httpRequest
	 *
	 * @return void
	 */
	public function handleClose(Entities\Clients\IClient $client, Http\IRequest $httpRequest): void;

	/**
	 * If there is an error with one of the sockets, or somewhere in the application where an Exception is thrown,
	 * the Exception is sent back down the stack, handled by the Server and bubbled back up the application through this method
	 *
	 * @param Entities\Clients\IClient $client
	 * @param Http\IRequest $httpRequest
	 * @param Throwable $ex
	 *
	 * @return void
	 */
	public function handleError(Entities\Clients\IClient $client, Http\IRequest $httpRequest, Throwable $ex): void;

	/**
	 * Triggered when a client sends data through the socket
	 *
	 * @param Entities\Clients\IClient $from
	 * @param Http\IRequest $httpRequest
	 * @param string $message
	 *
	 * @return void
	 */
	public function handleMessage(Entities\Clients\IClient $from, Http\IRequest $httpRequest, string $message): void;

	/**
	 * @return array
	 * @todo This method may be removed in future version (note that will not break code, just make some code obsolete)
	 * If any component in a stack supports a WebSocket sub-protocol return each supported in an array
	 */
	public function getSubProtocols(): array;

}
