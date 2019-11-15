<?php
/**
 * IValidator.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Encoding
 * @since          1.0.0
 *
 * @date           04.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Encoding;

/**
 * Encoding validation interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Encoding
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IValidator
{
	/**
	 * Verify a string matches the encoding type
	 *
	 * @param string $str      The string to check
	 * @param string $encoding The encoding type to check against
	 *
	 * @return bool
	 */
	public function checkEncoding(string $str, string $encoding) : bool;
}
