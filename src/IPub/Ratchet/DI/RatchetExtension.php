<?php
/**
 * RatchetExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\DI;

use Nette;
use Nette\DI;
use Nette\PhpGenerator as Code;

use React;

use IPub;
use IPub\Ratchet;
use IPub\Ratchet\Application;
use IPub\Ratchet\Router;
use IPub\Ratchet\Server;
use IPub\Ratchet\Storage;

/**
 * Ratchet extension container
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class RatchetExtension extends DI\CompilerExtension
{
	/**
	 * @var array
	 */
	private $defaults = [
		'server'  => [
			'httpHost' => 'localhost',
			'port'     => 8888,
			'address'  => '0.0.0.0',
			'type'     => 'message',    // message|wamp
		],
		'session' => TRUE,
		'routes'  => [],
		'mapping' => [],
	];

	/**
	 * {@inheritdoc}
	 */
	public function loadConfiguration()
	{
		parent::loadConfiguration();

		// Get container builder
		$builder = $this->getContainerBuilder();
		// Get extension configuration
		$configuration = $this->getConfig($this->defaults);

		$controllerFactory = $builder->addDefinition($this->prefix('controllerFactory'))
			->setClass(Application\IControllerFactory::class)
			->setFactory(Application\ControllerFactory::class, [new Nette\DI\Statement(
				Application\ControllerFactoryCallback::class
			)]);

		if ($configuration['mapping']) {
			$controllerFactory->addSetup('setMapping', [$configuration['mapping']]);
		}

		$builder->addDefinition($this->prefix('session.factory'))
			->setClass(Ratchet\Session\SessionFactory::class)
			->setArguments([
				'handler' => $builder->getDefinition($builder->getByType(\SessionHandlerInterface::class))
			]);

		$builder->addDefinition($this->prefix('storage.connection'))
			->setClass(Storage\Connections::class);

		// Http routes collector
		$builder->addDefinition($this->prefix('router'))
			->setClass(Router\IRouter::class)
			->setFactory(Router\RouteList::class);

		if ($configuration['server']['type'] === 'wamp') {
			$application = $builder->addDefinition($this->prefix('application'))
				->setClass(Application\PubSubApplication::class);

		} else {
			$application = $builder->addDefinition($this->prefix('application'))
				->setClass(Application\MessageApplication::class);
		}

		// React event loop
		$loop = $builder->addDefinition($this->prefix('server.loop'))
			->setClass(React\EventLoop\LoopInterface::class)
			->setFactory('React\EventLoop\Factory::create');

		// Ratchet server
		$builder->addDefinition($this->prefix('server.server'))
			->setClass(Server\Server::class, [
				$application,
				$loop,
				$configuration['server']['httpHost'],
				$configuration['server']['port'],
				$configuration['server']['address'],
				$configuration['session'],
			]);

	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile()
	{
		parent::beforeCompile();

		// Get container builder
		$builder = $this->getContainerBuilder();

		// Get application router
		$router = $builder->getDefinition($this->prefix('router'));

		// Init collections
		$routersFactories = [];

		foreach ($builder->findByTag('ipub.ratchet.routes') as $routerService => $priority) {
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

		$allControllers = [];

		foreach ($builder->findByType(Application\UI\IController::class) as $def) {
			$allControllers[$def->getClass()] = $def;
		}

		foreach ($allControllers as $def) {
			$def->addTag(Nette\DI\Extensions\InjectExtension::TAG_INJECT)
				->addTag('ipub.ratchet.controller', $def->getClass());
		}
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'ratchet')
	{
		$config->onCompile[] = function (Nette\Configurator $config, DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new RatchetExtension());
		};
	}
}
