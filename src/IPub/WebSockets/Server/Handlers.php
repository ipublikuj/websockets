<?php
/**
 * Handlers.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           02.12.20
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use Throwable;

use Nette;

use Psr\Log;

use React;

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
 */
final class Handlers
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

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
	 * @var Log\LoggerInterface|Log\NullLogger|NULL
	 */
	private $logger;

	/**
	 * @var bool
	 */
	private $isRunning = FALSE;

	/**
	 * @param Wrapper $application
	 * @param Clients\Storage $clientStorage
	 * @param Clients\IClientFactory $clientFactory
	 * @param Log\LoggerInterface|NULL $logger
	 */
	public function __construct(
		Wrapper $application,
		FlashWrapper $flashApplication,
		Clients\Storage $clientStorage,
		Clients\IClientFactory $clientFactory,
		?Log\LoggerInterface $logger = NULL
	) {
		$this->clientStorage = $clientStorage;
		$this->clientFactory = $clientFactory;
		$this->application = $application;
		$this->flashApplication = $flashApplication;
		$this->logger = $logger === NULL ? new Log\NullLogger : $logger;
	}

	/**
	 * @param React\Socket\ConnectionInterface $connection
	 *
	 * @return void
	 *
	 * @throws Exceptions\StorageException
	 */
	public function handleConnect(React\Socket\ConnectionInterface $connection) : void
	{
		$client = $this->clientFactory->create((int) $connection->stream, $connection);

		$this->clientStorage->addClient($client->getId(), $client);

		try {
			$this->application->handleOpen($client);

			$connection->on('data', function (string $chunk) use ($connection) {
				$this->handleData($chunk, $connection, $this->application);
			});

			$connection->on('end', function () use ($connection) {
				$this->handleEnd($connection, $this->application);
			});

			$connection->on('error', function (Throwable $ex) use ($connection) {
				$this->handleError($ex, $connection, $this->application);
			});

		} catch (Throwable $ex) {
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
	 * @param React\Socket\ConnectionInterface $connection
	 *
	 * @return void
	 *
	 * @throws Exceptions\StorageException
	 */
	public function handleFlashConnect(React\Socket\ConnectionInterface $connection) : void
	{
		$client = $this->clientFactory->create((int) $connection->stream, $connection);

		$this->clientStorage->addClient($client->getId(), $client);

		try {
			$this->flashApplication->handleOpen($client);

			$connection->on('data', function (string $chunk) use ($connection) {
				$this->handleData($chunk, $connection, $this->flashApplication);
			});

			$connection->on('end', function () use ($connection) {
				$this->handleEnd($connection, $this->flashApplication);
			});

			$connection->on('error', function (Throwable $ex) use ($connection) {
				$this->handleError($ex, $connection, $this->flashApplication);
			});

		} catch (Throwable $ex) {
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

		} catch (Throwable $ex) {
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

		} catch (Throwable $ex) {
			$this->handleError($ex, $connection, $application);
		}
	}

	/**
	 * @param Throwable $ex
	 * @param React\Socket\ConnectionInterface $connection
	 * @param IWrapper $application
	 *
	 * @return void
	 */
	private function handleError(Throwable $ex, React\Socket\ConnectionInterface $connection, IWrapper $application) : void
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

		} catch (Throwable $ex) {
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
