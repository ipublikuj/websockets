<?php
/**
 * IController.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           17.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application\Controller;

use IPub;
use IPub\WebSockets\Application;
use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Session;

/**
 * WebSockets controller interface
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IController
{
	/**
	 * @param Application\Request $request
	 *
	 * @return Responses\IResponse
	 */
	function run(Application\Request $request) : Responses\IResponse;

	/**
	 * @return string
	 */
	function getName() : string;
}
