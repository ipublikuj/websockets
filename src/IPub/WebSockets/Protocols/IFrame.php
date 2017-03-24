<?php
/**
 * IFrame.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols;

/**
 * Communication frame interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IFrame extends IData
{
	/**
	 * Add incoming data to the frame from peer
	 *
	 * @param string $buffer
	 *
	 * @return void
	 */
	function addBuffer(string $buffer);

	/**
	 * Is this the final frame in a fragmented message?
	 *
	 * @return bool
	 */
	function isFinal() : bool;

	/**
	 * Is the payload masked?
	 *
	 * @return bool
	 */
	function isMasked() : bool;

	/**
	 * @return int
	 */
	function getOpCode() : int;

	/**
	 * 32-bit string
	 *
	 * @return string
	 */
	function getMaskingKey() : string;
}
