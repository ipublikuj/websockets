<?php declare(strict_types = 1);

/**
 * StartEvent.php
 *
 * @copyright      More in LICENSE.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           15.11.19
 */

namespace IPub\WebSockets\Events\Server;

use IPub\WebSockets\Server;
use Symfony\Contracts\EventDispatcher;

/**
 * Server start event
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class CreateEvent extends EventDispatcher\Event
{

	/** @var Server\Server */
	private $server;

	/**
	 * @param Server\Server $server
	 */
	public function __construct(
		Server\Server $server
	) {
		$this->server = $server;
	}

	/**
	 * @return Server\Server
	 */
	public function getServer(): Server\Server
	{
		return $this->server;
	}

}
