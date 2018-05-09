<?php
/**
 * IRequest.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 * @since          1.0.0
 *
 * @date           04.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Http;

use Nette\Http;

/**
 * HTTP request interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IRequest extends Http\IRequest
{
	/**
	 * @param Http\UrlScript $url
	 *
	 * @return void
	 */
	function setUrl(Http\UrlScript $url) : void;

	/**
	 * @param float $version
	 *
	 * @return void
	 */
	function setProtocolVersion(float $version) : void;

	/**
	 * @return float|NULL
	 */
	function getProtocolVersion() : ?float;
}
