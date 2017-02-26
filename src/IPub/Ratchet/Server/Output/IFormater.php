<?php
/**
 * IFormatter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           26.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Server\Output;

/**
 * Ratchet server output formater interface
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IFormatter
{
	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function write(string $message);

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function writeln(string $message);

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function success(string $message);

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function error(string $message);

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function warning(string $message);

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function note(string $message);

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function caution(string $message);
}
