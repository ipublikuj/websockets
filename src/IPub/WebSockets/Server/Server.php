<?php
/**
 * Server.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use Closure;
use Throwable;

use Nette;

use Psr\Log;

use React;
use React\EventLoop;

use IPub\WebSockets\Clients;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;

/**
 * WebSocket server
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onStart(EventLoop\LoopInterface $eventLoop, Server $server)
 * @method onStop(EventLoop\LoopInterface $eventLoop, Server $server)
 */
final class Server
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	const VERSION = 'IPub/WebSockets/1.0.0';

	/**
	 * @var Closure
	 */
	public $onStart = [];

	/**
	 * @var Closure
	 */
	public $onStop = [];

	/**
	 * @var Handlers
	 */
	private $handlers;

	/**
	 * @var EventLoop\LoopInterface
	 */
	private $loop;

	/**
	 * @var Configuration
	 */
	private $configuration;

	/**
	 * @var Log\LoggerInterface|Log\NullLogger|NULL
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $isRunning = FALSE;

	/**
	 * @param Handlers $handlers
	 * @param EventLoop\LoopInterface $loop
	 * @param Configuration $configuration
	 * @param Log\LoggerInterface|NULL $logger
	 */
	public function __construct(
		Handlers $handlers,
		EventLoop\LoopInterface $loop,
		Configuration $configuration,
		?Log\LoggerInterface $logger = NULL
	) {
		$this->loop = $loop;
		$this->configuration = $configuration;
		$this->handlers = $handlers;
		$this->logger = $logger === NULL ? new Log\NullLogger : $logger;
	}

	/**
	 * Run IO server
	 *
	 * @return void
	 */
	public function run() : void
	{
		$client = $this->configuration->getAddress() . ':' . $this->configuration->getPort();
		$socket = new React\Socket\Server($client, $this->loop);

		if ($this->configuration->isSSLEnabled()) {
			$socket = new React\Socket\SecureServer($socket, $this->loop, $this->configuration->getSSLConfiguration());
		}

		$socket->on('connection', function (React\Socket\ConnectionInterface $connection) {
			$this->handlers->handleConnect($connection);
		});

		$socket->on('error', function (Throwable $ex) {
			$this->logger->error('Could not establish connection: ' . $ex->getMessage());
		});

		if ($this->configuration->getPort() === 80) {
			$client = '0.0.0.0:843';

		} else {
			$client = $this->configuration->getAddress() . ':8843';
		}

		$flashSocket = new React\Socket\Server($client, $this->loop);

		$flashSocket->on('connection', function (React\Socket\ConnectionInterface $connection) {
			$this->handlers->handleFlashConnect($connection);
		});

		$this->logger->debug('Starting IPub\WebSockets');
		$this->logger->debug(sprintf('Launching WebSockets WS Server on: %s:%s', $this->configuration->getAddress(), $this->configuration->getPort()));

		$this->onStart($this->loop, $this);

		$this->isRunning = TRUE;

		$this->loop->run();
	}


	/**
	 * Stop IO server
	 *
	 * @return void
	 */
	public function stop() : void
	{
		$this->onStop($this->loop, $this);

		$this->loop->stop();

		$this->isRunning = FALSE;
	}

}
