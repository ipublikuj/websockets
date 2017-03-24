<?php
/**
 * IRequest.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
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
		S100_CONTINUE = 100,
		S101_SWITCHING_PROTOCOLS = 101,
		S102_PROCESSING = 102,
		S200_OK = 200,
		S201_CREATED = 201,
		S202_ACCEPTED = 202,
		S203_NON_AUTHORITATIVE_INFORMATION = 203,
		S204_NO_CONTENT = 204,
		S205_RESET_CONTENT = 205,
		S206_PARTIAL_CONTENT = 206,
		S207_MULTI_STATUS = 207,
		S208_ALREADY_REPORTED = 208,
		S226_IM_USED = 226,
		S300_MULTIPLE_CHOICES = 300,
		S301_MOVED_PERMANENTLY = 301,
		S302_FOUND = 302,
		S303_SEE_OTHER = 303,
		S303_POST_GET = 303,
		S304_NOT_MODIFIED = 304,
		S305_USE_PROXY = 305,
		S307_TEMPORARY_REDIRECT = 307,
		S308_PERMANENT_REDIRECT = 308,
		S400_BAD_REQUEST = 400,
		S401_UNAUTHORIZED = 401,
		S402_PAYMENT_REQUIRED = 402,
		S403_FORBIDDEN = 403,
		S404_NOT_FOUND = 404,
		S405_METHOD_NOT_ALLOWED = 405,
		S406_NOT_ACCEPTABLE = 406,
		S407_PROXY_AUTHENTICATION_REQUIRED = 407,
		S408_REQUEST_TIMEOUT = 408,
		S409_CONFLICT = 409,
		S410_GONE = 410,
		S411_LENGTH_REQUIRED = 411,
		S412_PRECONDITION_FAILED = 412,
		S413_REQUEST_ENTITY_TOO_LARGE = 413,
		S414_REQUEST_URI_TOO_LONG = 414,
		S415_UNSUPPORTED_MEDIA_TYPE = 415,
		S416_REQUESTED_RANGE_NOT_SATISFIABLE = 416,
		S417_EXPECTATION_FAILED = 417,
		S421_MISDIRECTED_REQUEST = 421,
		S422_UNPROCESSABLE_ENTITY = 422,
		S423_LOCKED = 423,
		S424_FAILED_DEPENDENCY = 424,
		S426_UPGRADE_REQUIRED = 426,
		S428_PRECONDITION_REQUIRED = 428,
		S429_TOO_MANY_REQUESTS = 429,
		S431_REQUEST_HEADER_FIELDS_TOO_LARGE = 431,
		S451_UNAVAILABLE_FOR_LEGAL_REASONS = 451,
		S500_INTERNAL_SERVER_ERROR = 500,
		S501_NOT_IMPLEMENTED = 501,
		S502_BAD_GATEWAY = 502,
		S503_SERVICE_UNAVAILABLE = 503,
		S504_GATEWAY_TIMEOUT = 504,
		S505_HTTP_VERSION_NOT_SUPPORTED = 505,
		S506_VARIANT_ALSO_NEGOTIATES = 506,
		S507_INSUFFICIENT_STORAGE = 507,
		S508_LOOP_DETECTED = 508,
		S510_NOT_EXTENDED = 510,
		S511_NETWORK_AUTHENTICATION_REQUIRED = 511;

	/**
	 * Sets HTTP response code.
	 *
	 * @param int $code
	 * @param string|NULL $reason
	 *
	 * @return void
	 */
	function setCode(int $code, string $reason = NULL);

	/**
	 * Returns HTTP response code
	 *
	 * @return int
	 */
	function getCode() : int;

	/**
	 * Adds HTTP header
	 *
	 * @param string $name header name
	 * @param string $value header value
	 *
	 * @return void
	 */
	function addHeader(string $name, string $value);

	/**
	 * Returns value of an HTTP header
	 *
	 * @param string $header
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	function getHeader($header, $default = NULL);

	/**
	 * Returns a list of headers to sent
	 *
	 * @return array (name => value)
	 */
	function getHeaders() : array;

	/**
	 * @return string
	 */
	function getReason() : string;

	/**
	 * @param string|NULL $body
	 */
	function setBody(string $body = NULL);
}
