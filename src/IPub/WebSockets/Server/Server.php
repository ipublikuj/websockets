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

use Nette;

use Psr\Log;

use React;
use React\EventLoop;

use IPub\WebSockets\Clients;
use IPub\WebSockets\Entities;

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
	 * @var \Closure
	 */
	public $onStart = [];

	/**
	 * @var \Closure
	 */
	public $onStop = [];

	/**
	 * @var IWrapper
	 */
	private $application;

	/**
	 * @var FlashWrapper
	 */
	private $flashApplication;

	/**
	 * @var Clients\Storage
	 */
	private $clientStorage;

	/**
	 * @var Clients\IClientFactory
	 */
	private $clientFactory;

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
	 * @param Wrapper $application
	 * @param FlashWrapper $flashApplication
	 * @param EventLoop\LoopInterface $loop
	 * @param Configuration $configuration
	 * @param Clients\Storage $clientStorage
	 * @param Clients\IClientFactory $clientFactory
	 * @param Log\LoggerInterface|NULL $logger
	 */
	public function __construct(
		Wrapper $application,
		FlashWrapper $flashApplication,
		EventLoop\LoopInterface $loop,
		Configuration $configuration,
		Clients\Storage $clientStorage,
		Clients\IClientFactory $clientFactory,
		?Log\LoggerInterface $logger = NULL
	) {
		$this->clientStorage = $clientStorage;
		$this->clientFactory = $clientFactory;
		$this->loop = $loop;
		$this->configuration = $configuration;
		$this->application = $application;
		$this->flashApplication = $flashApplication;
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
			$this->handleConnect($connection, $this->application);
		});

		$socket->on('error', function (\Exception $ex) {
			$this->logger->error('Could not establish connection: '. $ex->getMessage());
		});

		if ($this->configuration->getPort() === 80) {
			$client = '0.0.0.0:843';

		} else {
			$client = $this->configuration->getAddress() . ':8843';
		}

		$flashSocket = new React\Socket\Server($client, $this->loop);

		$flashSocket->on('connection', function (React\Socket\ConnectionInterface $connection) {
			$this->handleConnect($connection, $this->flashApplication);
		});

		$this->logger->debug('Starting IPub\WebSockets');
		$this->logger->debug(sprintf('Launching WebSockets WS Server on: %s:%s', $this->configuration->getHttpHost(), $this->configuration->getPort()));

		$this->onStart($this->loop, $this);

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
	}

	/**
	 * @param React\Socket\ConnectionInterface $connection
	 * @param IWrapper $application
	 *
	 * @return void
	 */
	private function handleConnect(React\Socket\ConnectionInterface $connection, IWrapper $application) : void
	{
		$client = $this->clientFactory->create((int) $connection->stream, $connection);

		$this->clientStorage->addClient($client->getId(), $client);

		try {
			$application->handleOpen($client);

			$connection->on('data', function (string $chunk) use ($connection, $application) {
				$this->handleData($chunk, $connection, $application);
			});

			$connection->on('end', function () use ($connection, $application) {
				$this->handleEnd($connection, $application);
			});

			$connection->on('error', function (\Exception $ex) use ($connection, $application) {
				$this->handleError($ex, $connection, $application);
			});

		} catch (\Exception $ex) {
			$context = [
				'code'   => $ex->getCode(),
				'file'   => $ex->getFile(),
				'client' => (int) $connection->stream,
			];

			$this->logger->error($ex->getMessage(), $context);

			$connection->end();
		}
	}

	/**
	 * @param string $data
	 * @param React\Socket\ConnectionInterface $connection
	 * @param IWrapper $application
	 *
	 * @return void
	 */
	private function handleData(string $data, React\Socket\ConnectionInterface $connection, IWrapper $application) : void
	{
		try {
			$client = $this->clientStorage->getClient((int) $connection->stream);

			$application->handleMessage($client, $data);

		} catch (\Exception $ex) {
			$this->handleError($ex, $connection, $application);
		}
	}

	/**
	 * @param React\Socket\ConnectionInterface $connection
	 * @param IWrapper $application
	 *
	 * @return void
	 */
	private function handleEnd(React\Socket\ConnectionInterface $connection, IWrapper $application) : void
	{
		try {
			$client = $this->clientStorage->getClient((int) $connection->stream);

			$application->handleClose($client);

		} catch (\Exception $ex) {
			$this->handleError($ex, $connection, $application);
		}
	}

	/**
	 * @param \Exception $ex
	 * @param React\Socket\ConnectionInterface $connection
	 * @param IWrapper $application
	 *
	 * @return void
	 */
	private function handleError(\Exception $ex, React\Socket\ConnectionInterface $connection, IWrapper $application) : void
	{
		try {
			$client = $this->clientStorage->getClient((int) $connection->stream);

			$context = [
				'code'    => $ex->getCode(),
				'file'    => $ex->getFile(),
				'client'  => (int) $connection->stream,
				'request' => $client->getRequest(),
			];

			$this->logger->error($ex->getMessage(), $context);

			$application->handleError($client, $ex);

		} catch (\Exception $ex) {
			$context = [
				'code'   => $ex->getCode(),
				'file'   => $ex->getFile(),
				'client' => (int) $connection->stream,
			];

			$this->logger->error($ex->getMessage(), $context);

			$connection->end();
		}
	}
}
