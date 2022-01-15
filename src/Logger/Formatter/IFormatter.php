<?php declare(strict_types = 1);

namespace IPub\WebSockets\Logger\Formatter;

/**
 * WebSockets server output formater interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Logger
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
	public function error(string $message): void;

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	public function warning(string $message): void;

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	public function note(string $message): void;

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	public function caution(string $message): void;

}
