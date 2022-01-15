<?php declare(strict_types = 1);

namespace IPub\WebSockets\Application;

use Closure;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;
use IPub\WebSockets\Router;
use IPub\WebSockets\Server;
use Nette;
use Psr\Log;
use Throwable;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control.
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onOpen(IApplication $application, Entities\Clients\IClient $client, Http\IRequest $httpRequest)
 * @method onClose(IApplication $application, Entities\Clients\IClient $client, Http\IRequest $httpRequest)
 * @method onMessage(IApplication $application, Entities\Clients\IClient $client, Http\IRequest $httpRequest, string $message)
 * @method onError(IApplication $application, Entities\Clients\IClient $client, Http\IRequest $httpRequest, Throwable $ex)
 */
abstract class Application implements IApplication
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/** @var Closure */
	public $onOpen = [];

	/** @var Closure */
	public $onClose = [];

	/** @var Closure */
	public $onMessage = [];

	/** @var Closure */
	public $onError = [];

	/** @var Router\IRouter */
	protected $router;

	/** @var Controller\IControllerFactory */
	protected $controllerFactory;

	/** @var Clients\IStorage */
	protected $clientsStorage;

	/** @var Log\LoggerInterface|Log\NullLogger|null */
	protected $logger;

	/**
	 * @param Router\IRouter $router
	 * @param Controller\IControllerFactory $controllerFactory
	 * @param Clients\IStorage $clientsStorage
	 * @param Log\LoggerInterface|null $logger
	 */
	public function __construct(
		Router\IRouter $router,
		Controller\IControllerFactory $controllerFactory,
		Clients\IStorage $clientsStorage,
		?Log\LoggerInterface $logger = null
	) {
		$this->router = $router;
		$this->controllerFactory = $controllerFactory;
		$this->clientsStorage = $clientsStorage;
		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleOpen(Entities\Clients\IClient $client, Http\IRequest $httpRequest): void
	{
		$this->logger->info(sprintf('New connection! (%s)', $client->getId()));

		$this->onOpen($this, $client, $httpRequest);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleClose(Entities\Clients\IClient $client, Http\IRequest $httpRequest): void
	{
		$this->onClose($this, $client, $httpRequest);

		$this->logger->info(sprintf('Connection %s has disconnected', $client->getId()));
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function handleError(Entities\Clients\IClient $client, Http\IRequest $httpRequest, Throwable $ex): void
	{
		$this->logger->info(sprintf('An error (%s) has occurred: %s', $ex->getCode(), $ex->getMessage()));

		$code = $ex->getCode();

		$this->onError($this, $client, $httpRequest, $ex);

		if ($code >= 400 && $code < 600) {
			$this->close($client, $code);

		} else {
			$client->close();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleMessage(Entities\Clients\IClient $from, Http\IRequest $httpRequest, string $message): void
	{
		$this->onMessage($this, $from, $httpRequest, $message);
	}

	/**
	 * @param Http\IRequest $httpRequest
	 * @param array $parameters
	 *
	 * @return Responses\IResponse|null
	 *
	 * @throws Exceptions\BadRequestException
	 * @throws Exceptions\InvalidControllerException
	 */
	protected function processMessage(Http\IRequest $httpRequest, array $parameters): ?Responses\IResponse
	{
		$appRequest = $this->router->match($httpRequest);

		if ($appRequest === null) {
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
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	protected function close(Entities\Clients\IClient $client, int $code = 400, array $additionalHeaders = []): void
	{
		$headers = array_merge([
			'X-Powered-By' => Server\Server::VERSION,
		], $additionalHeaders);

		$response = new Responses\ErrorResponse($code, $headers);

		$client->send($response);
		$client->close();
	}

}
