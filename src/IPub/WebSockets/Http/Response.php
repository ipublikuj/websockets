<?php
/**
 * Response.php
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

use Nette;

use IPub\WebSockets\Exceptions;

/**
 * HTTP response formater
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Response implements IResponse
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var int HTTP response code
	 */
	private $code = self::S200_OK;

	/**
	 * @var string
	 */
	private $reason;

	/**
	 * @var array
	 */
	private $headers = [];

	/**
	 * @var string|NULL
	 */
	private $body;

	/**
	 * @var array Array of reason phrases and their corresponding status codes
	 */
	private static $statusTexts = [
		100 => 'Continue',
		101 => 'Switching Protocols',
		102 => 'Processing',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		207 => 'Multi-Status',
		208 => 'Already Reported',
		226 => 'IM Used',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		307 => 'Temporary Redirect',
		308 => 'Permanent Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		422 => 'Unprocessable Entity',
		423 => 'Locked',
		424 => 'Failed Dependency',
		425 => 'Reserved for WebDAV advanced collections expired proposal',
		426 => 'Upgrade required',
		428 => 'Precondition Required',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
		506 => 'Variant Also Negotiates (Experimental)',
		507 => 'Insufficient Storage',
		508 => 'Loop Detected',
		510 => 'Not Extended',
		511 => 'Network Authentication Required',
	];

	/**
	 * @param int $code
	 * @param array $headers
	 * @param string|NULL $body
	 */
	public function __construct(int $code, array $headers = [], ?string $body = NULL)
	{
		$this->setCode($code);
		$this->setBody($body);

		$this->headers = $headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCode(int $code, ?string $reason = NULL) : void
	{
		$code = (int) $code;

		if ($code < 100 || $code > 599) {
			throw new Exceptions\InvalidArgumentException("Bad HTTP response '$code'.");
		}

		$this->code = $code;

		$this->reason = ($reason ?: (array_key_exists($code, self::$statusTexts) ? self::$statusTexts[$code] : 'Unknown status'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCode() : int
	{
		return $this->code;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addHeader(string $name, string $value) : void
	{
		$this->headers[$name] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeader($header, $default = NULL)
	{
		if (isset($this->headers[$header])) {
			return $this->headers[$header];
		}

		return $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaders() : array
	{
		return $this->headers;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getReason() : string
	{
		return $this->reason;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setBody(?string $body = NULL) : void
	{
		$this->body = $body;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		$message = 'HTTP/1.1 ' . $this->code . ' ' . $this->reason;

		foreach ($this->getHeaders() as $header => $value) {
			$message .= "\r\n" . $header . ': ' . $value;
		}

		$message .= "\r\n";

		if ($this->body !== NULL && strlen($this->body) < 2097152) {
			$message .= $this->body;
		}

		$message .= "\r\n";

		return $message;
	}
}
