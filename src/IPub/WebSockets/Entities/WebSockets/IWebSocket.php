<?php
/**
 * IWebSocket.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           02.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Entities\WebSockets;

use IPub;
use IPub\WebSockets\Protocols;

interface IWebSocket
{
	/**
	 * @param bool $state
	 *
	 * @return void
	 */
	function setEstablished(bool $state);

	/**
	 * @return bool
	 */
	function isEstablished() : bool;

	/**
	 * @param bool $state
	 *
	 * @return void
	 */
	function setClosing(bool $state);

	/**
	 * @return bool
	 */
	function isClosing() : bool;

	/**
	 * @return Protocols\IProtocol
	 */
	function getProtocol() : Protocols\IProtocol;

	/**
	 * @param Protocols\IMessage $message
	 *
	 * @return void
	 */
	function setMessage(Protocols\IMessage $message);

	/**
	 * @return Protocols\IMessage
	 */
	function getMessage() : Protocols\IMessage;

	/**
	 * @return void
	 */
	function destroyMessage();

	/**
	 * @return bool
	 */
	function hasMessage() : bool;

	/**
	 * @param Protocols\IFrame $frame
	 *
	 * @return void
	 */
	function setFrame(Protocols\IFrame $frame);

	/**
	 * @return Protocols\IFrame
	 */
	function getFrame() : Protocols\IFrame;

	/**
	 * @return void
	 */
	function destroyFrame();

	/**
	 * @return bool
	 */
	function hasFrame() : bool;
}
