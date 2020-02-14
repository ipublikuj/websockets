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
 * HTTP request
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Request extends Http\Request implements IRequest
{
	/**
	 * @var float
	 */
	private $protocolVersion;

	/**
	 * @var Http\UrlScript
	 */
	private $url;

	/**
	 * @param Http\UrlScript $url
	 * @param array|NULL $post
	 * @param array|NULL $files
	 * @param array|NULL $cookies
	 * @param array|NULL $headers
	 * @param string|NULL $method
	 * @param string|NULL $remoteAddress
	 * @param string|NULL $remoteHost
	 * @param null $rawBodyCallback
	 */
	public function __construct(
		Http\UrlScript $url,
		?array $post = NULL,
		?array $files = NULL,
		?array $cookies = NULL,
		?array $headers = NULL,
		?string $method = NULL,
		?string $remoteAddress = NULL,
		?string $remoteHost = NULL,
		$rawBodyCallback = NULL
	) {
		parent::__construct($url, $post, $files, $cookies, $headers, $method, $remoteAddress, $remoteHost, $rawBodyCallback);

		$this->url = $url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setUrl(Http\UrlScript $url) : void
	{
		$this->url = $url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUrl() : Http\UrlScript
	{
		return clone $this->url;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQuery(string $key = NULL)
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
	public function isSecured() : bool
	{
		return $this->url->getScheme() === 'wss';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setProtocolVersion(float $version) : void
	{
		$this->protocolVersion = $version;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProtocolVersion() : ?float
	{
		return $this->protocolVersion;
	}
}
