<?php
/**
 * StopEvent.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           15.11.19
 */

namespace IPub\WebSockets\Events\Server;

use Symfony\Contracts\EventDispatcher;

use React\EventLoop;

use IPub\WebSockets\Server;

/**
 * Server stop event
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class StopEvent extends EventDispatcher\Event
{
	/**
	 * @var EventLoop\LoopInterface
	 */
	private $eventLoop;

	/**
	 * @var Server\Server
	 */
	private $server;

	/**
	 * @param EventLoop\LoopInterface $eventLoop
	 * @param Server\Server $server
	 */
	public function __construct(
		EventLoop\LoopInterface $eventLoop,
		Server\Server $server
	) {
		$this->eventLoop = $eventLoop;
		$this->server = $server;
	}

	/**
	 * @return EventLoop\LoopInterface
	 */
	public function getEventLoop() : EventLoop\LoopInterface
	{
		return $this->eventLoop;
	}

	/**
	 * @return Server\Server
	 */
	public function getServer() : Server\Server
	{
		return $this->server;
	}
}
