<?php
/**
 * IWebSocket.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           02.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Entities\WebSockets;

use IPub\WebSockets\Protocols;

interface IWebSocket
{
	/**
	 * @param bool $state
	 *
	 * @return void
	 */
	public function setEstablished(bool $state) : void;

	/**
	 * @return bool
	 */
	public function isEstablished() : bool;

	/**
	 * @param bool $state
	 *
	 * @return void
	 */
	public function setClosing(bool $state) : void;

	/**
	 * @return bool
	 */
	public function isClosing() : bool;

	/**
	 * @return Protocols\IProtocol
	 */
	public function getProtocol() : Protocols\IProtocol;

	/**
	 * @param Protocols\IMessage $message
	 *
	 * @return void
	 */
	public function setMessage(Protocols\IMessage $message) : void;

	/**
	 * @return Protocols\IMessage
	 */
	public function getMessage() : Protocols\IMessage;

	/**
	 * @return void
	 */
	public function destroyMessage() : void;

	/**
	 * @return bool
	 */
	public function hasMessage() : bool;

	/**
	 * @param Protocols\IFrame $frame
	 *
	 * @return void
	 */
	public function setFrame(Protocols\IFrame $frame) : void;

	/**
	 * @return Protocols\IFrame
	 */
	public function getFrame() : Protocols\IFrame;

	/**
	 * @return void
	 */
	public function destroyFrame() : void;

	/**
	 * @return bool
	 */
	public function hasFrame() : bool;
}
