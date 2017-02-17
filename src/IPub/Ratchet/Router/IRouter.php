<?php
/**
 * IRouter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Router
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Router;

use Guzzle\Http;

use IPub;
use IPub\Ratchet\Application;

/**
 * Router interface
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Router
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IRouter
{
	/**
	 * Convert incoming message to the request, if not match return NULL
	 *
	 * @param Http\Message\RequestInterface $httpRequest
	 *
	 * @return Application\Request|NULL
	 */
	function match(Http\Message\RequestInterface $httpRequest);
}
