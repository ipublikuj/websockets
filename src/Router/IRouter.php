<?php declare(strict_types = 1);

namespace IPub\WebSockets\Router;

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
	 * Convert incoming message to the request, if not match return null
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return Application\Request|null
	 */
	public function match(Http\IRequest $httpRequest): ?Application\Request;

	/**
	 * Constructs absolute URL from Request object
	 *
	 * @param Application\IRequest $appRequest
	 *
	 * @return string|null
	 */
	public function constructUrl(Application\IRequest $appRequest): ?string;

}
