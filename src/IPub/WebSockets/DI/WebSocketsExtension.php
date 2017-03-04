<?php
/**
 * WebSocketsExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use Kdyby\Console;

use Psr\Log;

use React;

use IPub;
use IPub\WebSockets;
use IPub\WebSockets\Application;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Commands;
use IPub\WebSockets\Logger;
use IPub\WebSockets\Router;
use IPub\WebSockets\Server;

/**
 * WebSockets extension container
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method DI\ContainerBuilder getContainerBuilder()
 * @method array getConfig(array $default)
 * @method string prefix($id)
 */
final class WebSocketsExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	private $defaults = [
		'clients' => [
			'storage' => [
				'driver' => '@clients.driver.memory',
				'ttl'    => 0,
			],
		],
		'server'  => [
			'httpHost' => 'localhost',
			'port'     => 8080,
			'address'  => '0.0.0.0',
		],
		'routes'  => [],
		'mapping' => [],
	];

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration()
	{
		parent::loadConfiguration();

		/** @var DI\ContainerBuilder $builder */
		$builder = $this->getContainerBuilder();
		/** @var array $configuration */
		$configuration = $this->getConfig($this->defaults);

		/**
		 * CONTROLLERS
		 */

		$controllerFactory = $builder->addDefinition($this->prefix('controllers.factory'))
			->setClass(Application\Controller\IControllerFactory::class)
			->setFactory(Application\Controller\ControllerFactory::class);

		if ($configuration['mapping']) {
			$controllerFactory->addSetup('setMapping', [$configuration['mapping']]);
		}

		/**
		 * CLIENTS
		 */

		$builder->addDefinition($this->prefix('clients.driver.memory'))
			->setClass(Clients\Drivers\InMemory::class);

		$storageDriver = $configuration['clients']['storage']['driver'] === '@clients.driver.memory' ?
			$builder->getDefinition($this->prefix('clients.driver.memory')) :
			$builder->getDefinition($configuration['clients']['storage']['driver']);

		$builder->addDefinition($this->prefix('clients.storage'))
			->setClass(Clients\Storage::class)
			->setArguments([
				'ttl' => $configuration['clients']['storage']['ttl'],
			])
			->addSetup('?->setStorageDriver(?)', ['@' . $this->prefix('clients.storage'), $storageDriver]);

		/**
		 * ROUTING
		 */

		// Http routes collector
		$builder->addDefinition($this->prefix('routing.router'))
			->setClass(Router\IRouter::class)
			->setFactory(Router\RouteList::class);

		// Http routes generator
		$builder->addDefinition($this->prefix('routing.generator'))
			->setClass(Router\LinkGenerator::class);

		/**
		 * SERVER
		 */

		$application = $builder->addDefinition($this->prefix('server.wrapper'))
			->setClass(Server\Wrapper::class);

		$flashApplication = $builder->addDefinition($this->prefix('server.flashWrapper'))
			->setClass(Server\FlashWrapper::class);

		$flashApplication->addSetup('?->addAllowedAccess(?, 80)', [$flashApplication, $configuration['server']['httpHost']]);
		$flashApplication->addSetup('?->addAllowedAccess(?, ?)', [$flashApplication, $configuration['server']['httpHost'], $configuration['server']['port']]);

		$loop = $builder->addDefinition($this->prefix('server.loop'))
			->setClass(React\EventLoop\LoopInterface::class)
			->setFactory('React\EventLoop\Factory::create');

		$configuration = new Server\Configuration(
			$configuration['server']['httpHost'],
			$configuration['server']['port'],
			$configuration['server']['address']
		);

		if ($builder->findByType(Log\LoggerInterface::class) === []) {
			$builder->addDefinition($this->prefix('server.logger'))
				->setClass(Logger\Console::class);
		}

		$builder->addDefinition($this->prefix('server.server'))
			->setClass(Server\Server::class, [
				$application,
				$flashApplication,
				$loop,
				$configuration,
			]);

		// Define all console commands
		$commands = [
			'server' => Commands\ServerCommand::class,
		];

		foreach ($commands as $name => $cmd) {
			$builder->addDefinition($this->prefix('commands' . lcfirst($name)))
				->setClass($cmd)
				->addTag(Console\DI\ConsoleExtension::TAG_COMMAND);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile()
	{
		parent::beforeCompile();

		// Get container builder
		$builder = $this->getContainerBuilder();

		/**
		 * ROUTER CREATION
		 */

		// Get application router
		$router = $builder->getDefinition($this->prefix('routing.router'));

		// Init collections
		$routersFactories = [];

		foreach ($builder->findByTag('ipub.websockets.routes') as $routerService => $priority) {
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
					$factory = new DI\Statement(['@' . $routerService, 'createRouter']);

					$router->addSetup('offsetSet', [NULL, $factory]);
				}
			}
		}

		/**
		 * CONTROLLERS INJECTS
		 */

		$allControllers = [];

		foreach ($builder->findByType(Application\Controller\IController::class) as $def) {
			$allControllers[$def->getClass()] = $def;
		}

		foreach ($allControllers as $def) {
			$def->addTag(Nette\DI\Extensions\InjectExtension::TAG_INJECT)
				->addTag('ipub.websockets.controller', $def->getClass());
		}
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'websockets')
	{
		$config->onCompile[] = function (Nette\Configurator $config, DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new WebSocketsExtension());
		};
	}
}
