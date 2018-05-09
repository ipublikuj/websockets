<?php
/**
 * IData.php
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
 * Communication data interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IData
{
	/**
	 * Determine if the message is complete or still fragmented
	 *
	 * @return bool
	 */
	function isCoalesced() : bool;

	/**
	 * Get the number of bytes the payload is set to be
	 *
	 * @return int
	 */
	function getPayloadLength() : int;

	/**
	 * Get the payload (message) sent from peer
	 *
	 * @return string
	 */
	function getPayload() : string;

	/**
	 * Get raw contents of the message
	 *
	 * @return string
	 */
	function getContents() : string;
}
