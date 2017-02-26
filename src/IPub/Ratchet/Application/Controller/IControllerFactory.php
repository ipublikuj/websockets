<?php
/**
 * IControllerFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           15.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application\Controller;

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * Responsible for creating a new instance of given controller
 *
 * @package        iPublikuj:Ratchet!
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
	function getControllerClass(string &$name);

	/**
	 * Creates new controller instance
	 *
	 * @param string $name
	 *
	 * @return IController
	 */
	function createController(string $name) : IController;
}
