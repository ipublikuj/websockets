<?php
/**
 * Request.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           16.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application;

use Nette;

/**
 * Controller request
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @property string $controllerName
 * @property array $parameters
 */
class Request implements IRequest
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

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
	 * {@inheritdoc}
	 */
	public function setControllerName(string $name)
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getControllerName() : string
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParameters(array $params)
	{
		$this->params = $params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters() : array
	{
		return $this->params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameter(string $key)
	{
		return isset($this->params[$key]) ? $this->params[$key] : NULL;
	}
}
