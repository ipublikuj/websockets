<?php declare(strict_types = 1);

namespace IPub\WebSockets\Application;

/**
 * Controller request interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IRequest
{

	/**
	 * Sets the controller name
	 *
	 * @param string $name
	 *
	 * @return void
	 */
	public function setControllerName(string $name): void;

	/**
	 * Retrieve the controller name
	 *
	 * @return string
	 */
	public function getControllerName(): string;

	/**
	 * Sets variables provided to the controller
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function setParameters(array $params): void;

	/**
	 * Returns all variables provided to the controller (usually via URL)
	 *
	 * @return array
	 */
	public function getParameters(): array;

	/**
	 * Returns a parameter provided to the controller
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getParameter(string $key);

}
