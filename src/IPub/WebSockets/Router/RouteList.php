<?php
/**
 * RouteList.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Router
 * @since          1.0.0
 *
 * @date           15.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Router;

use Nette\Utils;

use IPub;
use IPub\WebSockets\Application;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;

/**
 * WebSockets routes list
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @author         David Grudl (https://davidgrudl.com)
 */
class RouteList extends Utils\ArrayList implements IRouter
{
	/**
	 * @var array
	 */
	private $cachedRoutes;

	/**
	 * @var string
	 */
	private $module;

	/**
	 * @param string|NULL $module
	 */
	public function __construct(string $module = NULL)
	{
		$this->module = $module ? $module . ':' : '';
	}

	/**
	 * Maps HTTP request to a application Request object
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return Application\Request|NULL
	 */
	public function match(Http\IRequest $httpRequest)
	{
		/** @var IRouter $route */
		foreach ($this as $route) {
			$appRequest = $route->match($httpRequest);

			if ($appRequest !== NULL) {
				$name = $appRequest->getControllerName();

				if (strncmp($name, 'IPub:', 5)) {
					$appRequest->setControllerName($this->module . $name);
				}

				return $appRequest;
			}
		}

		return NULL;
	}

	/**
	 * Constructs absolute URL from Request object
	 *
	 * @param Application\IRequest $appRequest
	 *
	 * @return string|NULL
	 */
	public function constructUrl(Application\IRequest $appRequest)
	{
		if ($this->cachedRoutes === NULL) {
			$this->warmupCache();
		}

		if ($this->module) {
			if (strncmp($tmp = $appRequest->getControllerName(), $this->module, strlen($this->module)) === 0) {
				$appRequest = clone $appRequest;
				$appRequest->setControllerName(substr($tmp, strlen($this->module)));

			} else {
				return NULL;
			}
		}

		$controller = $appRequest->getControllerName();

		if (!isset($this->cachedRoutes[$controller])) {
			$controller = '*';
		}

		/** @var IRouter $route */
		foreach ($this->cachedRoutes[$controller] as $route) {
			$url = $route->constructUrl($appRequest);

			if ($url !== NULL) {
				return $url;
			}
		}

		return NULL;
	}

	/**
	 * Adds the router
	 *
	 * @param mixed $index
	 * @param IRouter $route
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function offsetSet($index, $route)
	{
		if (!$route instanceof IRouter) {
			throw new Exceptions\InvalidArgumentException('Argument must be IRouter descendant.');
		}

		parent::offsetSet($index, $route);
	}

	/**
	 * @return string
	 */
	public function getModule() : string
	{
		return $this->module;
	}

	/**
	 * @return void
	 */
	public function warmupCache()
	{
		$routes = [];
		$routes['*'] = [];

		foreach ($this as $route) {
			$controllers = $route instanceof Route && is_array($tmp = $route->getTargetControllers())
				? $tmp
				: array_keys($routes);

			foreach ($controllers as $controller) {
				if (!isset($routes[$controller])) {
					$routes[$controller] = $routes['*'];
				}
				$routes[$controller][] = $route;
			}
		}

		$this->cachedRoutes = $routes;
	}
}
