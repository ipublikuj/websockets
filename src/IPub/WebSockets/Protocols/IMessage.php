<?php
/**
 * IMessage.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols;

/**
 * Communication message interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IMessage extends IData
{
	/**
	 * @param IFrame $fragment
	 *
	 * @return void
	 */
	public function addFrame(IFrame $fragment) : void;

	/**
	 * @return int
	 */
	public function getOpCode() : int;
}
