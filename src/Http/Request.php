<?php declare(strict_types = 1);

namespace IPub\WebSockets\Http;

use Nette\Http;

/**
 * HTTP request
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Request extends Http\Request implements IRequest
{

	/** @var float */
	private $protocolVersion;

	/** @var Http\UrlScript */
	private $url;

	/**
	 * @param Http\UrlScript $url
	 * @param array $post
	 * @param array $files
	 * @param array $cookies
	 * @param array $headers
	 * @param string $method
	 * @param string|null $remoteAddress
	 * @param string|null $remoteHost
	 * @param null $rawBodyCallback
	 */
	public function __construct(
		Http\UrlScript $url,
		array $post = [],
		array $files = [],
		array $cookies = [],
		array $headers = [],
		string $method = 'GET',
		?string $remoteAddress = null,
		?string $remoteHost = null,
		$rawBodyCallback = null
	) {
		parent::__construct($url, $post, $files, $cookies, $headers, $method, $remoteAddress, $remoteHost, $rawBodyCallback);

		$this->url = $url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setUrl(Http\UrlScript $url): void
	{
		$this->url = $url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUrl(): Http\UrlScript
	{
		return clone $this->url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery(?string $key = null): mixed
	{
		if (func_num_args() === 0) {
			return $this->url->getQueryParameters();

		} else {
			return $this->url->getQueryParameter($key);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSecured(): bool
	{
		return $this->url->getScheme() === 'wss';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProtocolVersion(float $version): void
	{
		$this->protocolVersion = $version;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProtocolVersion(): ?float
	{
		return $this->protocolVersion;
	}

}
