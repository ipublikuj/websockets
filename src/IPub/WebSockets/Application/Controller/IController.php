<?php
/**
 * IController.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           17.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application\Controller;

use IPub\WebSockets\Application;
use IPub\WebSockets\Application\Responses;

/**
 * WebSockets controller interface
 *
 * @package        iPublikuj:WebSockets!
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
	public function run(Application\Request $request) : Responses\IResponse;

	/**
	 * @return string
	 */
	public function getName() : string;
}
