<?php
/**
 * WebSocketsExtension.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\DI;

use Nette;
use Nette\DI;
use Nette\Schema;

use Psr\Log;

use React;

use Symfony\Component\EventDispatcher;

use IPub\WebSockets;
use IPub\WebSockets\Application;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Commands;
use IPub\WebSockets\Events;
use IPub\WebSockets\Logger;
use IPub\WebSockets\Router;
use IPub\WebSockets\Server;

/**
 * WebSockets extension container
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class WebSocketsExtension extends DI\CompilerExtension
{
	// Define tag string for routes
	const TAG_WEBSOCKETS_ROUTES = 'ipub.websockets.routes';

	/**
	 * {@inheritdoc}
	 */
	public function getConfigSchema() : Schema\Schema
	{
		return Schema\Expect::structure([
			'clients' => Schema\Expect::structure([
				'storage' => Schema\Expect::structure([
					'driver' => Schema\Expect::string('@clients.driver.memory'),
					'ttl'    => Schema\Expect::int(0),
				]),
			]),
			'server'  => Schema\Expect::structure([
				'httpHost' => Schema\Expect::string('localhost'),
				'port'     => Schema\Expect::int(8080),
				'address'  => Schema\Expect::string('0.0.0.0'),
				'secured'  => Schema\Expect::structure([
					'enable'      => Schema\Expect::bool(FALSE),
					'sslSettings' => Schema\Expect::array([]),
				]),
			]),
			'routes'  => Schema\Expect::array([]),
			'mapping' => Schema\Expect::array([]),
			'loop'    => Schema\Expect::anyOf(Schema\Expect::string(), Schema\Expect::type(DI\Definitions\Statement::class))->nullable(),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration() : void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$configuration = $this->getConfig();

		/**
		 * CONTROLLERS
		 */

		$controllerFactory = $builder->addDefinition($this->prefix('controllers.factory'))
			->setType(Application\Controller\IControllerFactory::class)
			->setFactory(Application\Controller\ControllerFactory::class);

		if ($configuration->mapping) {
			$controllerFactory->addSetup('setMapping', [$configuration->mapping]);
		}

		/**
		 * CLIENTS
		 */

		$builder->addDefinition($this->prefix('clients.factory'))
			->setType(Clients\ClientFactory::class);

		$builder->addDefinition($this->prefix('clients.driver.memory'))
			->setType(Clients\Drivers\InMemory::class);

		$storageDriver = $configuration->clients->storage->driver === '@clients.driver.memory' ?
			$builder->getDefinition($this->prefix('clients.driver.memory')) :
			$builder->getDefinition($configuration->clients->storage->driver);

		$builder->addDefinition($this->prefix('clients.storage'))
			->setType(Clients\Storage::class)
			->setArguments([
				'ttl' => $configuration->clients->storage->ttl,
			])
			->addSetup('?->setStorageDriver(?)', ['@' . $this->prefix('clients.storage'), $storageDriver]);

		/**
		 * ROUTING
		 */

		// Http routes collector
		$router = $builder->addDefinition($this->prefix('routing.router'))
			->setType(Router\IRouter::class)
			->setFactory(Router\RouteList::class);

		foreach ($configuration->routes as $mask => $action) {
			$router->addSetup('$service[] = new IPub\WebSockets\Router\Route(?, ?);', [$mask, $action]);
		}

		// Http routes generator
		$builder->addDefinition($this->prefix('routing.generator'))
			->setType(Router\LinkGenerator::class);

		/**
		 * SERVER
		 */

		$application = $builder->addDefinition($this->prefix('server.wrapper'))
			->setType(Server\Wrapper::class);

		$flashApplication = $builder->addDefinition($this->prefix('server.flashWrapper'))
			->setType(Server\FlashWrapper::class);

		$flashApplication->addSetup('?->addAllowedAccess(?, 80)', [
			$flashApplication,
			$configuration->server->httpHost,
		]);
		$flashApplication->addSetup('?->addAllowedAccess(?, ?)', [
			$flashApplication,
			$configuration->server->httpHost,
			$configuration->server->port,
		]);

		if ($configuration->loop === NULL) {
			if ($builder->getByType(React\EventLoop\LoopInterface::class) === NULL) {
				$loop = $builder->addDefinition($this->prefix('server.loop'))
					->setType(React\EventLoop\LoopInterface::class)
					->setFactory('React\EventLoop\Factory::create');

			} else {
				$loop = $builder->getDefinitionByType(React\EventLoop\LoopInterface::class);
			}

		} else {
			$loop = is_string($configuration->loop) ? new DI\Definitions\Statement($configuration->loop) : $configuration->loop;
		}

		$serverConfiguration = $builder->addDefinition($this->prefix('server.configuration'))
			->setType(Server\Configuration::class)
			->setArguments([
				'port'        => $configuration->server->port,
				'address'     => $configuration->server->address,
				'enableSSL'   => $configuration->server->secured->enable,
				'sslSettings' => $configuration->server->secured->sslSettings,
			]);

		if ($builder->findByType(Log\LoggerInterface::class) === []) {
			$builder->addDefinition($this->prefix('server.logger'))
				->setType(Logger\Console::class);
		}

		$builder->addDefinition($this->prefix('server.server'))
			->setType(Server\Server::class)
			->setArguments([
				$application,
				$flashApplication,
				$loop,
				$serverConfiguration,
			]);

		if (class_exists('Symfony\Component\Console\Command\Command')) {
			// Define all console commands
			$commands = [
				'server' => Commands\ServerCommand::class,
			];

			foreach ($commands as $name => $cmd) {
				$builder->addDefinition($this->prefix('commands.' . lcfirst($name)))
					->setType($cmd);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile() : void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();

		/**
		 * ROUTER CREATION
		 */

		// Get application router
		$router = $builder->getDefinition($this->prefix('routing.router'));

		// Init collections
		$routersFactories = [];

		foreach ($builder->findByTag(self::TAG_WEBSOCKETS_ROUTES) as $routerService => $priority) {
			// Priority is not defined...
			if (is_bool($priority)) {
				// ...use default value
				$priority = 100;
			}

			$routersFactories[$priority][$routerService] = $routerService;
		}

		// Sort routes by priority
		if (!empty($routersFactories)) {
			krsort($routersFactories, SORT_NUMERIC);

			foreach ($routersFactories as $priority => $items) {
				ksort($items, SORT_STRING);
				$routersFactories[$priority] = $items;
			}

			// Process all routes services by priority...
			foreach ($routersFactories as $priority => $items) {
				// ...and by service name...
				foreach ($items as $routerService) {
					$factory = new DI\Definitions\Statement(['@' . $routerService, 'createRouter']);

					$router->addSetup('offsetSet', [NULL, $factory]);
				}
			}
		}

		/**
		 * CONTROLLERS INJECTS
		 */

		$allControllers = [];

		foreach ($builder->findByType(Application\Controller\IController::class) as $def) {
			$allControllers[$def->getType()] = $def;
		}

		foreach ($allControllers as $def) {
			$def->addTag(Nette\DI\Extensions\InjectExtension::TAG_INJECT)
				->addTag('ipub.websockets.controller', $def->getType());
		}

		/**
		 * EVENTS
		 */

		if (interface_exists('Symfony\Component\EventDispatcher\EventDispatcherInterface')) {
			$dispatcher = $builder->getDefinition($builder->getByType(EventDispatcher\EventDispatcherInterface::class));

			$application = $builder->getDefinition($builder->getByType(Application\Application::class));
			assert($application instanceof DI\Definitions\ServiceDefinition);

			$application->addSetup('?->onOpen[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Application\OpenEvent::class),
			]);
			$application->addSetup('?->onClose[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Application\CloseEvent::class),
			]);
			$application->addSetup('?->onMessage[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Application\MessageEvent::class),
			]);
			$application->addSetup('?->onError[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Application\ErrorEvent::class),
			]);

			$server = $builder->getDefinition($builder->getByType(Server\Server::class));
			assert($server instanceof DI\ServiceDefinition);

			$server->addSetup('?->onStart[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Server\StartEvent::class),
			]);
			$server->addSetup('?->onStop[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Server\StopEvent::class),
			]);

			$serverWrapper = $builder->getDefinition($builder->getByType(Server\Wrapper::class));
			assert($serverWrapper instanceof DI\ServiceDefinition);

			$serverWrapper->addSetup('?->onClientConnected[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Wrapper\ClientConnectEvent::class),
			]);
			$serverWrapper->addSetup('?->onClientDisconnected[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Wrapper\ClientDisconnectEvent::class),
			]);
			$serverWrapper->addSetup('?->onClientError[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Wrapper\ClientErrorEvent::class),
			]);
			$serverWrapper->addSetup('?->onIncomingMessage[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Wrapper\IncommingMessageEvent::class),
			]);
			$serverWrapper->addSetup('?->onAfterIncomingMessage[] = function() {?->dispatch(new ?(...func_get_args()));}', [
				'@self',
				$dispatcher,
				new Nette\PhpGenerator\PhpLiteral(Events\Wrapper\AfterIncommingMessageEvent::class),
			]);
		}
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(
		Nette\Configurator $config,
		string $extensionName = 'websockets'
	) : void {
		$config->onCompile[] = function (Nette\Configurator $config, DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new WebSocketsExtension());
		};
	}
}
