<?php
/**
 * IRequest.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           19.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application;

/**
 * Controller request interface
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IRequest
{
	/**
	 * Sets the controller name
	 *
	 * @param string
	 *
	 * @return void
	 */
	function setControllerName(string $name);

	/**
	 * Retrieve the controller name
	 *
	 * @return string
	 */
	function getControllerName() : string;

	/**
	 * Sets variables provided to the controller
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	function setParameters(array $params);

	/**
	 * Returns all variables provided to the controller (usually via URL)
	 *
	 * @return array
	 */
	function getParameters() : array;

	/**
	 * Returns a parameter provided to the controller
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	function getParameter(string $key);
}
