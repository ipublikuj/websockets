<?php declare(strict_types = 1);

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
	public function setUrl(Http\UrlScript $url): void;

	/**
	 * @param float $version
	 *
	 * @return void
	 */
	public function setProtocolVersion(float $version): void;

	/**
	 * @return float|null
	 */
	public function getProtocolVersion(): ?float;

}
