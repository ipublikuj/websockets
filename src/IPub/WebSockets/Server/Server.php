<?php
/**
 * Server.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
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

use IPub;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Entities;

/**
 * WebSocket server
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onStart(EventLoop\LoopInterface $eventLoop, Server $server)
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
	 * @var IWrapper
	 */
	private $application;

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
		Log\LoggerInterface $logger = NULL
	) {
		$this->clientStorage = $clientStorage;
		$this->clientFactory = $clientFactory;
		$this->loop = $loop;
		$this->configuration = $configuration;
		$this->application = $application;
		$this->logger = $logger === NULL ? new Log\NullLogger : $logger;

		$client = $configuration->getAddress() . ':' . $configuration->getPort();
		$socket = new React\Socket\Server($client, $this->loop);

		if ($configuration->isSSLEnabled()) {
			$socket = new React\Socket\SecureServer($socket, $this->loop, $this->configuration->getSSLConfiguration());
		}

		$socket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($application) {
			$this->handleConnect($connection, $application);
		});
		$socket->on('error', function (\Exception $ex) {
			$this->logger->error('Could not establish connection: '. $ex->getMessage());
		});

		if ($configuration->getPort() === 80) {
			$client = '0.0.0.0:843';

		} else {
			$client = $configuration->getAddress() . ':8843';
		}

		$flashSocket = new React\Socket\Server($client, $this->loop);

		$flashSocket->on('connection', function (React\Socket\ConnectionInterface $connection) use ($flashApplication) {
			$this->handleConnect($connection, $flashApplication);
		});
	}

	/**
	 * Run IO server
	 *
	 * @return void
	 */
	public function run()
	{
		$this->logger->debug('Starting IPub\WebSockets');
		$this->logger->debug(sprintf('Launching WebSockets WS Server on: %s:%s', $this->configuration->getHttpHost(), $this->configuration->getPort()));

		$this->onStart($this->loop, $this);

		$this->loop->run();
	}

	/**
	 * @param React\Socket\ConnectionInterface $connection
	 * @param IWrapper $application
	 *
	 * @return void
	 */
	private function handleConnect(React\Socket\ConnectionInterface $connection, IWrapper $application)
	{
		$client = $this->clientFactory->create((int) $connection->stream, $connection);

		$this->clientStorage->addClient($client->getId(), $client);

		try {
			$application->onOpen($client);

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
	private function handleData(string $data, React\Socket\ConnectionInterface $connection, IWrapper $application)
	{
		try {
			$client = $this->clientStorage->getClient((int) $connection->stream);

			$application->onMessage($client, $data);

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
	private function handleEnd(React\Socket\ConnectionInterface $connection, IWrapper $application)
	{
		try {
			$client = $this->clientStorage->getClient((int) $connection->stream);

			$application->onClose($client);

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
	private function handleError(\Exception $ex, React\Socket\ConnectionInterface $connection, IWrapper $application)
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

			$application->onError($client, $ex);

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
