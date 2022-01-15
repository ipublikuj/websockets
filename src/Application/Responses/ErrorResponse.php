<?php declare(strict_types = 1);

namespace IPub\WebSockets\Application\Responses;

use IPub\WebSockets\Exceptions;
use Nette;
use Nette\Utils;

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

	/** @var Utils\ArrayHash */
	private $headers;

	/** @var int */
	private $statusCode;

	/**
	 * @param int $statusCode
	 * @param Utils\ArrayHash|array|null $headers
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function __construct(int $statusCode, $headers = null)
	{
		$this->headers = new Utils\ArrayHash();

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
	public function setStatus(int $statusCode): void
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
	public function setHeaders(array $headers): void
	{
		$this->headers = new Utils\ArrayHash();

		foreach ($headers as $key => $value) {
			$this->addHeader($key, $value);
		}
	}

	/**
	 * @param string $header
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function addHeader(string $header, $value): void
	{
		$this->headers->offsetSet($header, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(): ?array
	{
		$headers = [];
		$headers[] = 'HTTP/1.1 ' . $this->statusCode;

		foreach ($this->headers as $key => $value) {
			$headers[] = $key . ':' . $value;
		}

		return $headers;
	}

	/**
	 * @return string
	 *
	 * @throws Nette\Utils\JsonException
	 */
	public function __toString()
	{
		return Utils\Json::encode($this->create());
	}

}
