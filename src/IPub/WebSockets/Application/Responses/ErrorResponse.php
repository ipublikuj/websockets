<?php
/**
 * ErrorResponse.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Responses
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application\Responses;

use Nette;
use Nette\Utils;

use IPub\WebSockets\Exceptions;

/**
 * Communication error response
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Responses
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ErrorResponse implements IResponse
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Utils\ArrayHash
	 */
	private $headers;

	/**
	 * @var int
	 */
	private $statusCode;

	public function __construct($statusCode, $headers = NULL, $body = NULL)
	{
		$this->headers = new Utils\ArrayHash;

		$this->setStatus($statusCode);

		if ($headers) {
			if (is_array($headers)) {
				$this->setHeaders($headers);

			} elseif ($headers instanceof Utils\ArrayHash) {
				$this->setHeaders((array) $headers);

			} else {
				throw new Exceptions\BadResponseException('Invalid headers argument received');
			}
		}
	}

	/**
	 * @param int $statusCode
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setStatus(int $statusCode) : void
	{
		if ($statusCode < 100 || $statusCode > 599) {
			throw new Exceptions\InvalidArgumentException(sprintf('Bad HTTP response "%s".', $statusCode));
		}

		$this->statusCode = $statusCode;
	}

	/**
	 * @param array $headers
	 *
	 * @return void
	 */
	public function setHeaders(array $headers) : void
	{
		$this->headers = new Utils\ArrayHash;

		foreach ($headers as $key => $value) {
			$this->addHeader($key, $value);
		}
	}

	/**
	 * @param string $header
	 * @param $value
	 *
	 * @return void
	 */
	public function addHeader(string $header, $value) : void
	{
		$this->headers->offsetSet($header, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create() : ?string
	{
		$headers = [];
		$headers[] = 'HTTP/1.1 ' . $this->statusCode;

		foreach ($this->headers as $key=>$value) {
			$headers[] = $key .':'. $value;
		}

		return implode("\r\n", $headers) . "\r\n";
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->create();
	}
}
