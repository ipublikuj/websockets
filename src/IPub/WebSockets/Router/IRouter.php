<?php
/**
 * IRouter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Router
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Router;

use IPub;
use IPub\WebSockets\Application;
use IPub\WebSockets\Http;

/**
 * Router interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IRouter
{
	/**
	 * Convert incoming message to the request, if not match return NULL
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return Application\Request|NULL
	 */
	function match(Http\IRequest $httpRequest);

	/**
	 * Constructs absolute URL from Request object
	 *
	 * @param Application\IRequest $appRequest
	 *
	 * @return string|NULL
	 */
	function constructUrl(Application\IRequest $appRequest);
}
