<?php
/**
 * IRequest.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
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
 * @package        iPublikuj:WebSocket!
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
	function setUrl(Http\UrlScript $url);

	/**
	 * @param float $version
	 *
	 * @return void
	 */
	function setProtocolVersion(float $version);

	/**
	 * @return float|NULL
	 */
	function getProtocolVersion();
}
