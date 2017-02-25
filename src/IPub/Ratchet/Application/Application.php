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

use IPub;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Application\UI;
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
	public function onOpen(Clients\Client $client)
	{
		echo "New connection! ({$client->getId()})\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Clients\Client $client)
	{
		echo "Connection {$client->getId()} has disconnected\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(Clients\Client $client, \Exception $ex)
	{
		Debugger::log($ex);

		echo "An error has occurred: ". $ex->getFile() . $ex->getLine() ."\n";

		$code = $ex->getCode();

		if ($code >= 400 && $code < 600) {
			$this->close($client, $code);

		} else {
			$client->close();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Clients\Client $from, $msg)
	{
		$appRequest = $this->router->match($from->getRequest());

		if ($appRequest === NULL) {
			throw new Exceptions\BadRequestException('Invalid message - router cant create request.');
		}

		if (is_array($msg)) {
			$appRequest->setParameters(array_merge($appRequest->getParameters(), $msg));
		}

		$appRequest->setParameters(array_merge($appRequest->getParameters(), ['client' => $from]));

		$controllerName = $appRequest->getControllerName();
		$controllerClass = $this->controllerFactory->getControllerClass($controllerName);

		if (!is_subclass_of($controllerClass, UI\IController::class)) {
			throw new Exceptions\BadRequestException(sprintf('%s must be implementation of %s.', $controllerClass, UI\IController::class));
		}

		/** @var UI\IController $controller */
		$controller = $this->controllerFactory->createController($controllerName);

		$response = $controller->run($appRequest);

		/** @var Clients\Client $connection */
		foreach ($this->clientsStorage as $client) {
			$client->send($response);
		}
	}

	/**
	 * Close a connection with an HTTP response
	 *
	 * @param Clients\Client $client
	 * @param int $code HTTP status code
	 * @param array $additionalHeaders
	 *
	 * @return void
	 */
	protected function close(Clients\Client $client, int $code = 400, array $additionalHeaders = [])
	{
		$headers = array_merge([
			'X-Powered-By' => \Ratchet\VERSION
		], $additionalHeaders);

		$response = new Responses\ErrorResponse($code, $headers);

		$client->send($response);
		$client->close();
	}
}
