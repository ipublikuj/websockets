<?php
/**
 * LinkGenerator.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Router
 * @since          1.0.0
 *
 * @date           27.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Router;

use Nette;

use IPub;
use IPub\WebSockets\Application;
use IPub\WebSockets\Application\Controller;
use IPub\WebSockets\Exceptions;

/**
 * WebSockets connection link generator
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @author         David Grudl (https://davidgrudl.com)
 */
class LinkGenerator
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var IRouter
	 */
	private $router;

	/**
	 * @var Controller\IControllerFactory|NULL
	 */
	private $controllerFactory;

	/**
	 * @param IRouter $router
	 * @param Controller\IControllerFactory|NULL $controllerFactory
	 */
	public function __construct(IRouter $router, Controller\IControllerFactory $controllerFactory = NULL)
	{
		$this->router = $router;
		$this->controllerFactory = $controllerFactory;
	}

	/**
	 * Generates URL to controller
	 *
	 * @param  string $destination in format "[[[module:]controller:]action] [#fragment]"
	 * @param  array $params
	 *
	 * @return string
	 *
	 * @throws Exceptions\InvalidLinkException
	 */
	public function link(string $destination, array $params = []) : string
	{
		if (!preg_match('~^([\w:]+):(\w*+)(#.*)?()\z~', $destination, $m)) {
			throw new Exceptions\InvalidLinkException(sprintf('Invalid link destination "%s".', $destination));
		}

		list(, $controller, $action, $frag) = $m;

		try {
			/** @var Controller\Controller $class */
			$class = $this->controllerFactory ? $this->controllerFactory->getControllerClass($controller) : NULL;

		} catch (Exceptions\InvalidControllerException $ex) {
			throw new Exceptions\InvalidLinkException($ex->getMessage(), NULL, $ex);
		}

		if (is_subclass_of($class, Controller\Controller::class)) {
			if (method_exists($class, $method = $class::formatActionMethod($action))) {
				$missing = [];

				Controller\Controller::argsToParams($class, $method, $params, [], $missing);

				if ($missing !== []) {
					/** @var \ReflectionParameter $rp */
					$rp = $missing[0];

					throw new Exceptions\InvalidLinkException(sprintf('Missing parameter $%s required by %s::%s()', $rp->getName(), $rp->getDeclaringClass()->getName(), $rp->getDeclaringFunction()->getName()));
				}

			} elseif (array_key_exists(0, $params)) {
				throw new Exceptions\InvalidLinkException(sprintf('Unable to pass parameters to action "%s:%s", missing corresponding method.', $controller, $action));
			}
		}

		if ($action !== '') {
			$params[Controller\Controller::ACTION_KEY] = $action;
		}

		$url = $this->router->constructUrl(new Application\Request($controller, $params));

		if ($url === NULL) {
			unset($params[Controller\Controller::ACTION_KEY]);

			$params = urldecode(http_build_query($params, NULL, ', '));

			throw new Exceptions\InvalidLinkException(sprintf('No route for %s(%s)', $destination, $params));
		}

		return $url . $frag;
	}

}
