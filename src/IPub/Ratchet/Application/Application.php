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
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;
use IPub\Ratchet\Session;
use IPub\Ratchet\Storage;

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
	 * @var Storage\Connections
	 */
	protected $connectionStorage;

	/**
	 * @param Router\IRouter $router
	 * @param IControllerFactory $controllerFactory
	 * @param Storage\Connections $connection
	 */
	public function __construct(
		Router\IRouter $router,
		IControllerFactory $controllerFactory,
		Storage\Connections $connection
	) {
		$this->router = $router;
		$this->controllerFactory = $controllerFactory;
		$this->connectionStorage = $connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(ConnectionInterface $conn)
	{
		$this->connectionStorage->addClient($conn);

		echo "New connection! ({$conn->resourceId})\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(ConnectionInterface $conn)
	{
		$this->connectionStorage->removeClient($conn);

		echo "Connection {$conn->resourceId} has disconnected\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(ConnectionInterface $conn, \Exception $ex)
	{
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
