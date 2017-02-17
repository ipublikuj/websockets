<?php
/**
 * Request.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           16.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application;

use Nette;

/**
 * Controller request
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @property string $controllerName
 * @property array $parameters
 * @property string|NULL $method
 */
class Request extends Nette\Object
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * @param string $name  fully qualified controller name (module:module:controller)
	 * @param array $params variables provided to the controller usually via URL
	 */
	public function __construct(string $name, array $params = [])
	{
		$this->name = $name;
		$this->params = $params;
	}

	/**
	 * Sets the controller name
	 *
	 * @param string
	 *
	 * @return void
	 */
	public function setControllerName(string $name)
	{
		$this->name = $name;
	}

	/**
	 * Retrieve the controller name
	 *
	 * @return string
	 */
	public function getControllerName() : string
	{
		return $this->name;
	}

	/**
	 * Sets variables provided to the controller
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public function setParameters(array $params)
	{
		$this->params = $params;
	}

	/**
	 * Returns all variables provided to the controller (usually via URL)
	 *
	 * @return array
	 */
	public function getParameters() : array
	{
		return $this->params;
	}

	/**
	 * Returns a parameter provided to the controller
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getParameter(string $key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : NULL;
	}
}
