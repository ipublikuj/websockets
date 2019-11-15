<?php
/**
 * IControllerFactory.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           15.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application\Controller;

use IPub\WebSockets\Exceptions;

/**
 * Responsible for creating a new instance of given controller
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IControllerFactory
{
	/**
	 * Generates and checks presenter class name
	 *
	 * @param string $name
	 *
	 * @return string class name
	 *
	 * @throws Exceptions\InvalidControllerException
	 */
	public function getControllerClass(string &$name);

	/**
	 * Creates new controller instance
	 *
	 * @param string $name
	 *
	 * @return IController
	 */
	public function createController(string $name) : IController;
}
