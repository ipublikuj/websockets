<?php
/**
 * RouteList.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Router
 * @since          1.0.0
 *
 * @date           15.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Router;

use Nette;
use Nette\Utils;

use Guzzle\Http;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Exceptions;

/**
 * WebSockets routes list
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @author         David Grudl (https://davidgrudl.com)
 */
class RouteList extends Utils\ArrayList implements IRouter
{
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
	 * @param Http\Message\RequestInterface $httpRequest
	 *
	 * @return Application\Request|NULL
	 */
	public function match(Http\Message\RequestInterface $httpRequest)
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
}
