<?php declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use Closure;
use Nette;
use Nette\Utils;
use Psr\Log;
use React;
use React\EventLoop;
use Throwable;

/**
 * WebSocket server
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onCreate(Server $server)
 * @method onStart(EventLoop\LoopInterface $loop, Server $server)
 * @method onStop(EventLoop\LoopInterface $loop, Server $server)
 */
final class Server
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	public const VERSION = 'IPub/WebSockets/1.0.0';

	/** @var Closure */
	public $onCreate = [];

	/** @var Closure */
	public $onStart = [];

	/** @var Closure */
	public $onStop = [];

	/** @var Handlers */
	private $handlers;

	/** @var EventLoop\LoopInterface */
	private $loop;

	/** @var Configuration */
	private $configuration;

	/** @var Log\LoggerInterface|Log\NullLogger|null */
	private $logger;

	/**
	 * @param Handlers $handlers
	 * @param EventLoop\LoopInterface $loop
	 * @param Configuration $configuration
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		Handlers $handlers,
		EventLoop\LoopInterface $loop,
		Configuration $configuration,
		?Log\LoggerInterface $logger = null
	) {
		$this->loop = $loop;
		$this->configuration = $configuration;
		$this->handlers = $handlers;
		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * Run IO server
	 *
	 * @param React\Socket\SocketServer|null $socket
	 * @param React\Socket\SocketServer|null $flashSocket
	 *
	 * @return void
	 */
	public function create($socket = null, $flashSocket = null): void
	{
		$client = $this->configuration->getAddress() . ':' . $this->configuration->getPort();

		if ($socket === null) {
			$socket = new React\Socket\SocketServer($client, [], $this->loop);

			if ($this->configuration->isSslEnabled()) {
				$socket = new React\Socket\SecureServer($socket, $this->loop, $this->configuration->getSslConfiguration());
			}
		}

		$socket->on('connection', function (React\Socket\ConnectionInterface $connection): void {
			if ($connection->getLocalAddress() === null) {
				return;
			}

			$parsed = Utils\ArrayHash::from((array) parse_url($connection->getLocalAddress()));

			if (
				property_exists($parsed, 'port')
				&& $parsed->offsetGet('port') === $this->configuration->getPort()
			) {
				$this->handlers->handleConnect($connection);
			}
		});

		$socket->on('error', function (Throwable $ex): void {
			$this->logger->error('Could not establish connection: ' . $ex->getMessage());
		});

		$flashPort = 8843;

		if ($this->configuration->getPort() === 80) {
			$flashPort = 843;
		}

		if ($flashSocket === null) {
			if ($this->configuration->getPort() === 80) {
				$flashClient = '0.0.0.0:' . $flashPort;

			} else {
				$flashClient = $this->configuration->getAddress() . ':' . $flashPort;
			}

			$flashSocket = new React\Socket\SocketServer($flashClient, [], $this->loop);
		}

		$flashSocket->on('connection', function (React\Socket\ConnectionInterface $connection) use($flashPort): void {
			if ($connection->getLocalAddress() === null) {
				return;
			}

			$parsed = Utils\ArrayHash::from((array) parse_url($connection->getLocalAddress()));

			if (
				property_exists($parsed, 'port')
				&& $parsed->offsetGet('port') === $flashPort
			) {
				$this->handlers->handleFlashConnect($connection);
			}
		});

		$flashSocket->on('error', function (Throwable $ex): void {
			$this->logger->error('Could not establish connection: ' . $ex->getMessage());
		});

		$this->onCreate($this);
	}

	public function run(): void
	{
		$this->onStart($this->loop, $this);

		$this->logger->debug('Starting IPub\WebSockets');
		$this->logger->debug(sprintf('Launching WebSockets WS Server on: %s:%s', $this->configuration->getAddress(), $this->configuration->getPort()));

		$this->loop->run();
	}

	public function stop(): void
	{
		$this->onStop($this->loop, $this);

		$this->loop->stop();
	}

}
