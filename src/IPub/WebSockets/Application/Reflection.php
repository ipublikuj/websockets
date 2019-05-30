<?php
/**
 * Reflection.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           30.05.19
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application;

use Nette;
use Nette\Reflection\ClassType;

use IPub\WebSockets\Exceptions;

/**
 * Helpers for Controllers
 *
 * @property-read string $name
 * @property-read string $fileName
 * @internal
 */
class Reflection extends \ReflectionClass
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @return array
	 */
	public static function combineArgs(\ReflectionFunctionAbstract $method, $args)
	{
		$res = [];

		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();

			list($type, $isClass) = self::getParameterType($param);

			if (isset($args[$name])) {
				$res[$i] = $args[$name];

				if (!self::convertType($res[$i], $type, $isClass)) {
					throw new Exceptions\BadRequestException(sprintf(
						'Argument $%s passed to %s() must be %s, %s given.',
						$name,
						($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
						$type === 'NULL' ? 'scalar' : $type,
						is_object($args[$name]) ? get_class($args[$name]) : gettype($args[$name])
					));
				}

			} elseif ($param->isDefaultValueAvailable()) {
				$res[$i] = $param->getDefaultValue();

			} elseif ($type === 'NULL' || $param->allowsNull()) {
				$res[$i] = NULL;

			} elseif ($type === 'array') {
				$res[$i] = [];

			} else {
				throw new Exceptions\BadRequestException(sprintf(
					'Missing parameter $%s required by %s()',
					$name,
					($method instanceof \ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName()
				));
			}
		}

		return $res;
	}

	/**
	 * Non data-loss type conversion.
	 *
	 * @param mixed
	 * @param string
	 *
	 * @return bool
	 */
	public static function convertType(&$val, $type, $isClass = FALSE)
	{
		if ($isClass) {
			return $val instanceof $type;

		} elseif ($type === 'callable') {
			return FALSE;

		} elseif ($type === 'NULL') { // means 'not array'
			return !is_array($val);

		} elseif ($type === 'array') {
			return is_array($val);

		} elseif (!is_scalar($val)) { // array, resource, null, etc.
			return FALSE;

		} else {
			$tmp = ($val === FALSE ? '0' : (string) $val);

			if ($type === 'double' || $type === 'float') {
				$tmp = preg_replace('#\.0*\z#', '', $tmp);
			}

			$orig = $tmp;

			settype($tmp, $type);

			if ($orig !== ($tmp === FALSE ? '0' : (string) $tmp)) {
				return FALSE; // data-loss occurs
			}

			$val = $tmp;
		}

		return TRUE;
	}

	/**
	 * Returns an annotation value.
	 *
	 * @return array|false
	 */
	public static function parseAnnotation(\Reflector $ref, $name)
	{
		if (!preg_match_all('#[\\s*]@' . preg_quote($name, '#') . '(?:\(\\s*([^)]*)\\s*\)|\\s|$)#', $ref->getDocComment(), $m)) {
			return FALSE;
		}

		static $tokens = ['true' => TRUE, 'false' => FALSE, 'null' => NULL];

		$res = [];

		foreach ($m[1] as $s) {
			foreach (preg_split('#\s*,\s*#', $s, -1, PREG_SPLIT_NO_EMPTY) ?: ['true'] as $item) {
				$res[] = array_key_exists($tmp = strtolower($item), $tokens) ? $tokens[$tmp] : $item;
			}
		}

		return $res;
	}

	/**
	 * @return array [string|null, bool]
	 */
	public static function getParameterType(\ReflectionParameter $param)
	{
		$def = gettype($param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL);

		if (PHP_VERSION_ID >= 70000) {
			return $param->hasType()
				? [PHP_VERSION_ID >= 70100 ? $param->getType()->getName() : (string) $param->getType(), !$param->getType()->isBuiltin()]
				: [$def, FALSE];

		} elseif ($param->isArray() || $param->isCallable()) {
			return [$param->isArray() ? 'array' : 'callable', FALSE];

		} else {
			try {
				return ($ref = $param->getClass()) ? [$ref->getName(), TRUE] : [$def, FALSE];

			} catch (\ReflectionException $e) {
				if (preg_match('#Class (.+) does not exist#', $e->getMessage(), $m)) {
					throw new \LogicException(sprintf(
						"Class %s not found. Check type hint of parameter $%s in %s() or 'use' statements.",
						$m[1],
						$param->getName(),
						$param->getDeclaringFunction()->getDeclaringClass()->getName() . '::' . $param->getDeclaringFunction()->getName()
					));
				}

				throw $e;
			}
		}
	}

	/**
	 * @return Nette\Reflection\Method
	 */
	public function getMethod($name)
	{
		return new Nette\Reflection\Method($this->getName(), $name);
	}

	public function __toString()
	{
		trigger_error(__METHOD__ . ' is deprecated.', E_USER_DEPRECATED);

		return $this->getName();
	}

	public function __get($name)
	{
		trigger_error("getReflection()->$name is deprecated.", E_USER_DEPRECATED);

		return (new ClassType($this->getName()))->$name;
	}

	public function __call($name, $args)
	{
		if (method_exists(ClassType::class, $name)) {
			trigger_error("getReflection()->$name() is deprecated, use Nette\\Reflection\\ClassType::from(\$presenter)->$name()", E_USER_DEPRECATED);

			return call_user_func_array([new ClassType($this->getName()), $name], $args);
		}

		Nette\Utils\ObjectMixin::strictCall(get_class($this), $name);
	}
}
