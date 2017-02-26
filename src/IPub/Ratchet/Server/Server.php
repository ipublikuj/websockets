<?php
/**
 * Server.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Server;

use Nette;

use Ratchet;

use React;
use React\EventLoop;

/**
 * Ratchet server for Nette - run instead of Nette application
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Server
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var EventLoop\LoopInterface
	 */
	private $loop;

	/**
	 * @var
	 */
	private $configuration;

	/**
	 * @var Ratchet\Server\IoServer
	 */
	private $server;

	/**
	 * @var Ratchet\Server\IoServer
	 */
	private $flashServer;

	/**
	 * @param Wrapper $application
	 * @param EventLoop\LoopInterface $loop
	 * @param Configuration $configuration
	 */
	public function __construct(
		Wrapper $application,
		EventLoop\LoopInterface $loop,
		Configuration $configuration
	) {
		$this->loop = $loop;
		$this->configuration = $configuration;

		$socket = new React\Socket\Server($this->loop);
		$socket->listen($configuration->getPort(), $configuration->getAddress());

		$component = new Ratchet\WebSocket\WsServer($application);

		$this->server = new Ratchet\Server\IoServer(
			new Ratchet\Http\HttpServer($component),
			$socket,
			$this->loop
		);

		$policy = new Ratchet\Server\FlashPolicy;
		$policy->addAllowedAccess($configuration->getHttpHost(), 80);
		$policy->addAllowedAccess($configuration->getHttpHost(), $configuration->getPort());

		$flashSock = new React\Socket\Server($this->loop);

		$this->flashServer = new Ratchet\Server\IoServer($policy, $flashSock);

		if ($configuration->getPort() === 80) {
			$flashSock->listen(843, '0.0.0.0');

		} else {
			$flashSock->listen(8843);
		}
	}

	/**
	 * Run IO server
	 * 
	 * @return void
	 */
	public function run()
	{
		$this->server->run();
	}
}
