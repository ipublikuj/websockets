<?php declare(strict_types = 1);

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

	/** @var string */
	private $name;

	/** @var array */
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
	public function setControllerName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getControllerName(): string
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setParameters(array $params): void
	{
		$this->params = $params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters(): array
	{
		return $this->params;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameter(string $key)
	{
		return $this->params[$key] ?? null;
	}

}
