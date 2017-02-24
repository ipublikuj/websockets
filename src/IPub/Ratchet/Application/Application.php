<?php
/**
 * Application.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application;

use Nette;

use Guzzle\Http\Message;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;
use IPub\Ratchet\Session;
use Tracy\Debugger;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control.
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
abstract class Application implements IApplication
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Router\IRouter
	 */
	protected $router;

	/**
	 * @var IControllerFactory
	 */
	protected $controllerFactory;

	/**
	 * @var Clients\IStorage
	 */
	protected $clientsStorage;

	/**
	 * @param Router\IRouter $router
	 * @param IControllerFactory $controllerFactory
	 * @param Clients\IStorage $clientsStorage
	 */
	public function __construct(
		Router\IRouter $router,
		IControllerFactory $controllerFactory,
		Clients\IStorage $clientsStorage
	) {
		$this->router = $router;
		$this->controllerFactory = $controllerFactory;
		$this->clientsStorage = $clientsStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(ConnectionInterface $conn)
	{
		$this->clientsStorage->addClient($this->clientsStorage->getStorageId($conn), $conn);

		echo "New connection! ({$conn->resourceId})\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(ConnectionInterface $conn)
	{
		$this->clientsStorage->removeClient($this->clientsStorage->getStorageId($conn));

		echo "Connection {$conn->resourceId} has disconnected\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(ConnectionInterface $conn, \Exception $ex)
	{
		Debugger::log($ex);

		echo "An error has occurred: ". $ex->getFile() . $ex->getLine() ."\n";

		$code = $ex->getCode();

		if ($code >= 400 && $code < 600) {
			$this->close($conn, $code);

		} else {
			$conn->close();
		}
	}

	/**
	 * Close a connection with an HTTP response
	 *
	 * @param ConnectionInterface $conn
	 * @param int $code HTTP status code
	 * @param array $additionalHeaders
	 *
	 * @return void
	 */
	protected function close(ConnectionInterface $conn, int $code = 400, array $additionalHeaders = [])
	{
		$headers = array_merge([
			'X-Powered-By' => \Ratchet\VERSION
		], $additionalHeaders);

		$response = new Message\Response($code, $headers);

		$conn->send((string) $response);
		$conn->close();
	}
}
