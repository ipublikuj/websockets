<?php
/**
 * ControllerFactoryCallback.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           19.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application;

use Nette;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Exceptions;

/**
 * ControllerFactory callback
 *
 * @internal
 */
class ControllerFactoryCallback
{
	/** @var Nette\DI\Container */
	private $container;

	public function __construct(Nette\DI\Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param ConnectionInterface $connection
	 * @param string $class
	 *
	 * @return UI\IController
	 * @throws Exceptions\InvalidControllerException
	 */
	public function __invoke(ConnectionInterface $connection, string $class) : UI\IController
	{
		$services = array_keys($this->container->findByTag('ipub.ratchet.controller'), $class);

		if (count($services) > 1) {
			throw new Exceptions\InvalidControllerException(sprintf('Multiple services of type "%s" found: %s.', $class, implode(', ', $services)));

		} elseif ($services === []) {
			/** @var UI\IController $controller */
			$controller = $this->container->createInstance($class);

			$this->container->callInjects($controller);

			return $controller;
		}

		return $this->container->createService($services[0]);
	}
}
