<?php declare(strict_types = 1);

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
	public function run(Application\Request $request): Responses\IResponse;

	/**
	 * @return string
	 */
	public function getName(): string;

}
