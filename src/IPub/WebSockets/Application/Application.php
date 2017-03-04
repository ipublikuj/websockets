<?php
/**
 * Application.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application;

use Nette;

use Psr\Log;

use IPub;
use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Application\Controller;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;
use IPub\WebSockets\Router;
use IPub\WebSockets\Server;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control.
 *
 * @package        iPublikuj:WebSocket!
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
	 * @var Log\LoggerInterface|Log\NullLogger|NULL
	 */
	protected $logger;

	/**
	 * @param Router\IRouter $router
	 * @param Controller\IControllerFactory $controllerFactory
	 * @param Clients\IStorage $clientsStorage
	 * @param Log\LoggerInterface|NULL $logger
	 */
	public function __construct(
		Router\IRouter $router,
		Controller\IControllerFactory $controllerFactory,
		Clients\IStorage $clientsStorage,
		Log\LoggerInterface $logger = NULL
	) {
		$this->router = $router;
		$this->controllerFactory = $controllerFactory;
		$this->clientsStorage = $clientsStorage;
		$this->logger = $logger === NULL ? new Log\NullLogger : $logger;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
	{
		$this->logger->info(sprintf('New connection! (%s)', $client->getId()));
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Entities\Clients\IClient $client, Http\IRequest $httpRequest)
	{
		$this->logger->info(sprintf('Connection %s has disconnected', $client->getId()));
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(Entities\Clients\IClient $client, Http\IRequest $httpRequest, \Exception $ex)
	{
		$this->logger->info(sprintf('An error (%s) has occurred: %s', $ex->getCode(), $ex->getMessage()));

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
	public function onMessage(Entities\Clients\IClient $from, Http\IRequest $httpRequest, string $message)
	{
		// 
	}

	/**
	 * @param Http\IRequest $httpRequest
	 * @param array $parameters
	 *
	 * @return Responses\IResponse|NULL
	 *
	 * @throws Exceptions\BadRequestException
	 */
	protected function processMessage(Http\IRequest $httpRequest, array $parameters)
	{
		$appRequest = $this->router->match($httpRequest);

		if ($appRequest === NULL) {
			throw new Exceptions\BadRequestException('Invalid message - router cant create request.');
		}

		$appRequest->setParameters(array_merge($appRequest->getParameters(), $parameters));

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
			'X-Powered-By' => Server\Server::VERSION
		], $additionalHeaders);

		$response = new Responses\ErrorResponse($code, $headers);

		$client->send((string) $response);
		$client->close();
	}
}
