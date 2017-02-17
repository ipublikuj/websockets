<?php
/**
 * ControllerFactory.php
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

namespace IPub\Ratchet\Application;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Exceptions;

/**
 * Default controller loader
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ControllerFactory extends Nette\Object implements IControllerFactory
{
	/**
	 * @var array[] of module => splited mask
	 */
	private $mapping = [
		'*'           => ['', '*Module\\', '*Controller'],
		'IPubRatchet' => ['IPubRatchetModule\\', '*\\', '*Controller'],
	];

	/**
	 * @var array
	 */
	private $cache = [];

	/**
	 * @var callable
	 */
	private $factory;

	/**
	 * @param callable|NULL $factory
	 */
	public function __construct(callable $factory = NULL)
	{
		$this->factory = $factory ?: function ($class) {
			return new $class;
		};
	}

	/**
	 * Creates new controller instance
	 *
	 * @param string $name
	 *
	 * @return UI\IController
	 */
	public function createController(string $name) : UI\IController
	{
		return call_user_func($this->factory, $this->getControllerClass($name));
	}

	/**
	 * Generates and checks controller class name
	 *
	 * @param string $name
	 *
	 * @return string class name
	 *
	 * @throws Exceptions\InvalidControllerException
	 */
	public function getControllerClass(string &$name) : string
	{
		if (isset($this->cache[$name])) {
			return $this->cache[$name];
		}

		if (!Utils\Strings::match($name, '#^[a-zA-Z\x7f-\xff][a-zA-Z0-9\x7f-\xff:]*\z#')) {
			throw new Exceptions\InvalidControllerException(sprintf('Controller name must be alphanumeric string, "%s" is invalid.', $name));
		}

		$class = $this->formatControllerClass($name);

		if (!class_exists($class)) {
			throw new Exceptions\InvalidControllerException(sprintf('Cannot load controller "%s", class "%s" was not found.', $name, $class));
		}

		$reflection = new \ReflectionClass($class);
		$class = $reflection->getName();

		if (!$reflection->implementsInterface(UI\IController::class)) {
			throw new Exceptions\InvalidControllerException(sprintf('Cannot load controller "%s", class "%s" is not IPub\\Ratchet\\Application\\IController implementor.', $name, $class));

		} elseif ($reflection->isAbstract()) {
			throw new Exceptions\InvalidControllerException(sprintf('Cannot load controller "%s", class "%s" is abstract.', $name, $class));
		}

		$this->cache[$name] = $class;

		if ($name !== ($realName = $this->unFormatControllerClass($class))) {
			trigger_error(sprintf('Case mismatch on controller name "%s", correct name is "%s".', $name, $realName), E_USER_WARNING);

			$name = $realName;
		}

		return $class;
	}

	/**
	 * Sets mapping as pairs [module => mask]
	 *
	 * @param array $mapping
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidStateException
	 */
	public function setMapping(array $mapping)
	{
		foreach ($mapping as $module => $mask) {
			if (is_string($mask)) {
				if (!preg_match('#^\\\\?([\w\\\\]*\\\\)?(\w*\*\w*?\\\\)?([\w\\\\]*\*\w*)\z#', $mask, $m)) {
					throw new Exceptions\InvalidStateException(sprintf('Invalid mapping mask "%s".', $mask));
				}

				$this->mapping[$module] = [$m[1], $m[2] ?: '*Module\\', $m[3]];

			} elseif (is_array($mask) && count($mask) === 3) {
				$this->mapping[$module] = [$mask[0] ? $mask[0] . '\\' : '', $mask[1] . '\\', $mask[2]];

			} else {
				throw new Exceptions\InvalidStateException(sprintf('Invalid mapping mask for module "%s".', $module));
			}
		}
	}

	/**
	 * Formats controller class name from its name
	 *
	 * @param string $controller
	 *
	 * @return string
	 *
	 * @internal
	 */
	public function formatControllerClass(string $controller) : string
	{
		$parts = explode(':', $controller);
		$mapping = isset($parts[1], $this->mapping[$parts[0]])
			? $this->mapping[array_shift($parts)]
			: $this->mapping['*'];

		while ($part = array_shift($parts)) {
			$mapping[0] .= str_replace('*', $part, $mapping[$parts ? 1 : 2]);
		}

		return $mapping[0];
	}

	/**
	 * Formats controller name from class name
	 *
	 * @param string $class
	 *
	 * @return string|bool
	 *
	 * @internal
	 */
	public function unFormatControllerClass(string $class)
	{
		foreach ($this->mapping as $module => $mapping) {
			$mapping = str_replace(['\\', '*'], ['\\\\', '(\w+)'], $mapping);

			if (preg_match("#^\\\\?$mapping[0]((?:$mapping[1])*)$mapping[2]\\z#i", $class, $matches)) {
				return ($module === '*' ? '' : $module . ':')
				. preg_replace("#$mapping[1]#iA", '$1:', $matches[1]) . $matches[3];
			}
		}

		return FALSE;
	}
}
