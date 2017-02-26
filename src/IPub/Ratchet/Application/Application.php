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
use IPub\Ratchet\Application\Controller;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Entities;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;

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
	 * @var Controller\IControllerFactory
	 */
	protected $controllerFactory;

	/**
	 * @var Clients\IStorage
	 */
	protected $clientsStorage;

	/**
	 * @param Router\IRouter $router
	 * @param Controller\IControllerFactory $controllerFactory
	 * @param Clients\IStorage $clientsStorage
	 */
	public function __construct(
		Router\IRouter $router,
		Controller\IControllerFactory $controllerFactory,
		Clients\IStorage $clientsStorage
	) {
		$this->router = $router;
		$this->controllerFactory = $controllerFactory;
		$this->clientsStorage = $clientsStorage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(Entities\Clients\IClient $client)
	{
		echo "New connection! ({$client->getId()})\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Entities\Clients\IClient $client)
	{
		echo "Connection {$client->getId()} has disconnected\n";
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(Entities\Clients\IClient $client, \Exception $ex)
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
	public function onMessage(Entities\Clients\IClient $from, string $message)
	{
		// 
	}

	/**
	 * @param Entities\Clients\IClient $from
	 * @param array $parameters
	 *
	 * @return Responses\IResponse|NULL
	 *
	 * @throws Exceptions\BadRequestException
	 */
	protected function processMessage(Entities\Clients\IClient $from, array $parameters)
	{
		$appRequest = $this->router->match($from->getRequest());

		if ($appRequest === NULL) {
			throw new Exceptions\BadRequestException('Invalid message - router cant create request.');
		}

		$appRequest->setParameters(array_merge($appRequest->getParameters(), $parameters));
		$appRequest->setParameters(array_merge($appRequest->getParameters(), ['client' => $from]));

		$controllerName = $appRequest->getControllerName();
		$controllerClass = $this->controllerFactory->getControllerClass($controllerName);

		if (!is_subclass_of($controllerClass, Controller\IController::class)) {
			throw new Exceptions\BadRequestException(sprintf('%s must be implementation of %s.', $controllerClass, Controller\IController::class));
		}

		/** @var Controller\IController $controller */
		$controller = $this->controllerFactory->createController($controllerName);

		return $controller->run($appRequest);
	}

	/**
	 * Close a connection with an HTTP response
	 *
	 * @param Entities\Clients\IClient $client
	 * @param int $code HTTP status code
	 * @param array $additionalHeaders
	 *
	 * @return void
	 */
	protected function close(Entities\Clients\IClient $client, int $code = 400, array $additionalHeaders = [])
	{
		$headers = array_merge([
			'X-Powered-By' => \Ratchet\VERSION
		], $additionalHeaders);

		$response = new Responses\ErrorResponse($code, $headers);

		$client->send($response);
		$client->close();
	}
}
