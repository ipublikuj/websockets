<?php
/**
 * SessionSerializerFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 * @since          1.0.0
 *
 * @date           24.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Session;

use Ratchet\Session\Serialize\HandlerInterface;

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * Session component provider factory
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class SessionSerializerFactory
{
	/**
	 * @return HandlerInterface
	 */
	public static function create() : HandlerInterface
	{
		$serialClass = '\\Ratchet\\Session\\Serialize\\' . self::toClassCase(ini_get('session.serialize_handler')) . 'Handler'; // awesome/terrible hack, eh?

		if (!class_exists($serialClass)) {
			throw new Exceptions\RuntimeException('Unable to parse session serialize handler');
		}

		return new $serialClass;
	}

	/**
	 * @param string $langDef Input to convert
	 *
	 * @return string
	 */
	private static function toClassCase(string $langDef) : string
	{
		return str_replace(' ', '', ucwords(str_replace('_', ' ', $langDef)));
	}
}
