<?php
/**
 * IMessage.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
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
 * @package        iPublikuj:WebSocket!
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
	function addFrame(IFrame $fragment);

	/**
	 * @return int
	 */
	function getOpCode() : int;
}
