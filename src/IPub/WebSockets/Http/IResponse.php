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

/**
 * HTTP response formater interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IResponse
{
	/** HTTP 1.1 response code */
	const
		S101_SWITCHING_PROTOCOLS = 101,
		S200_OK = 200,
		S400_BAD_REQUEST = 400,
		S413_REQUEST_ENTITY_TOO_LARGE = 413,
		S500_INTERNAL_SERVER_ERROR = 500;

	/**
	 * Sets HTTP response code.
	 *
	 * @param int $code
	 * @param string|NULL $reason
	 *
	 * @return void
	 */
	public function setCode(int $code, ?string $reason = NULL) : void;

	/**
	 * Returns HTTP response code
	 *
	 * @return int
	 */
	public function getCode() : int;

	/**
	 * Adds HTTP header
	 *
	 * @param string $name  header name
	 * @param string $value header value
	 *
	 * @return void
	 */
	public function addHeader(string $name, string $value) : void;

	/**
	 * Returns value of an HTTP header
	 *
	 * @param string $header
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function getHeader($header, $default = NULL);

	/**
	 * Returns a list of headers to sent
	 *
	 * @return array (name => value)
	 */
	public function getHeaders() : array;

	/**
	 * @return string
	 */
	public function getReason() : string;

	/**
	 * @param string|NULL $body
	 *
	 * @return void
	 */
	public function setBody(string $body = NULL) : void;
}
