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

use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;

use Nette;

use IPub\WebSockets\Exceptions;

/**
 * Helpers for Controllers
 *
 * @internal
 */
class Reflection
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @param ReflectionFunctionAbstract $method
	 * @param mixed[] $args
	 *
	 * @return array
	 *
	 * @throws Exceptions\BadRequestException
	 * @throws ReflectionException
	 */
	public static function combineArgs(ReflectionFunctionAbstract $method, array $args)
	{
		$res = [];

		foreach ($method->getParameters() as $i => $param) {
			$name = $param->getName();

			[$type, $isClass] = self::getParameterType($param);

			if (isset($args[$name])) {
				$res[$i] = $args[$name];

				if (!self::convertType($res[$i], $type, $isClass)) {
					throw new Exceptions\BadRequestException(sprintf(
						'Argument $%s passed to %s() must be %s, %s given.',
						$name,
						($method instanceof ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName(),
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
					($method instanceof ReflectionMethod ? $method->getDeclaringClass()->getName() . '::' : '') . $method->getName()
				));
			}
		}

		return $res;
	}

	/**
	 * Non data-loss type conversion.
	 *
	 * @param mixed $val
	 * @param string $type
	 * @param bool $isClass
	 *
	 * @return bool
	 */
	public static function convertType(&$val, string $type, bool $isClass = FALSE)
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
	 * @param ReflectionParameter $param
	 *
	 * @return array [string|null, bool]
	 *
	 * @throws ReflectionException
	 */
	public static function getParameterType(ReflectionParameter $param)
	{
		$def = gettype($param->isDefaultValueAvailable() ? $param->getDefaultValue() : NULL);

		return $param->hasType() ? [$param->getType()->getName(), !$param->getType()->isBuiltin()] : [$def, FALSE];
	}
}
