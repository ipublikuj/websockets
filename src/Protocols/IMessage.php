<?php declare(strict_types = 1);

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
	public function addFrame(IFrame $fragment): void;

	/**
	 * @return int
	 */
	public function getOpCode(): int;

}
