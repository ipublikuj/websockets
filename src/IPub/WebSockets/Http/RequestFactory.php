<?php
/**
 * RequestFactory.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 * @since          1.0.0
 *
 * @date           03.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Http;

use Nette;
use Nette\Http;

/**
 * HTTP request factory
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Http
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class RequestFactory
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @internal
	 */
	const CHARS = '\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}';

	/**
	 * Undefined method
	 *
	 * @internal
	 */
	const METHOD_EXTENDED = 'extended';

	const EOM = "\r\n\r\n";

	/**
	 * Cookie part names to snake_case array values
	 *
	 * @var array
	 */
	protected static $cookieParts = [
		'domain'      => 'Domain',
		'path'        => 'Path',
		'max_age'     => 'Max-Age',
		'expires'     => 'Expires',
		'version'     => 'Version',
		'secure'      => 'Secure',
		'port'        => 'Port',
		'discard'     => 'Discard',
		'comment'     => 'Comment',
		'comment_url' => 'Comment-Url',
		'http_only'   => 'HttpOnly',
	];

	/**
	 * The maximum number of bytes the request can be
	 * This is a security measure to prevent attacks
	 *
	 * @var int
	 */
	private $maxSize = 4096;

	/**
	 * @var bool
	 */
	private $binary = FALSE;

	/**
	 * @var array
	 */
	private $proxies = [];

	/**
	 * Whether PHP is running with FastCGI or not.
	 *
	 * @var bool
	 */
	private static $_fcgi = NULL;

	public function __construct()
	{
		if (NULL === self::$_fcgi) {
			self::$_fcgi = 'cgi-fcgi' === PHP_SAPI;
		}
	}

	/**
	 * @param bool $binary
	 *
	 * @return void
	 */
	public function setBinary(bool $binary = TRUE) : void
	{
		$this->binary = $binary;
	}

	/**
	 * @param array|string
	 *
	 * @return void
	 */
	public function setProxy($proxy) : void
	{
		$this->proxies = (array) $proxy;
	}

	/**
	 * @param string $packet
	 *
	 * @return IRequest|NULL
	 */
	public function createHttpRequest(string $packet) : ?IRequest
	{
		if (strlen($packet) > $this->maxSize) {
			throw new \OverflowException("Maximum buffer size of {$this->maxSize} exceeded parsing HTTP header");
		}

		if (!$this->isEom($packet)) {
			return NULL;
		}

		$parsedHeaders = explode("\r\n", $packet);

		$http = array_shift($parsedHeaders);

		$rawBody = NULL;

		foreach ($parsedHeaders as $i => $header) {
			if (trim($header) === '') {
				unset($parsedHeaders[$i]);

				$rawBody = trim(implode("\r\n", array_splice($parsedHeaders, $i)));

				break;
			}
		}

		if (preg_match('#^([^\s]+)\s+([^\s]+)\s+HTTP/(1\.(?:0|1))$#i', $http, $matches) === 0) {
			throw new \Exception(
				'HTTP headers are not well-formed: %s',
				0,
				$http
			);
		}

		switch ($method = strtoupper($matches[1])) {
			case Http\IRequest::DELETE:
			case Http\IRequest::GET:
			case Http\IRequest::HEAD:
			case Http\IRequest::OPTIONS:
			case Http\IRequest::PATCH:
			case Http\IRequest::POST:
			case Http\IRequest::PUT:
				// Nothing to do here
				break;

			default:
				$method = self::METHOD_EXTENDED;
		}

		$url = new Http\UrlScript($matches[2]);

		$httpVersion = (float) $matches[3];

		$headers = [];

		foreach ($parsedHeaders as $header) {
			list($name, $value) = explode(':', $header, 2);
			$headers[strtolower($name)] = trim($value);
		}

		// Check for the Host header
		if (isset($headers['host'])) {
			$url->setScheme('ws');

			if (strpos($headers['host'], ':') !== FALSE) {
				$hostParts = explode(':', $headers['host']);

				$url->setHost(trim($hostParts[0]));
				$url->setPort((int) trim($hostParts[1]));

				if ($url->getPort() == 443) {
					$url->setScheme('wss');
				}

			} else {
				$url->setHost($headers['host']);
			}
		}

		// Check if a query is present
		$path = $matches[2];
		$qpos = strpos($path, '?');

		if ($qpos) {
			$url->setQuery(substr($path, $qpos + 1));
			$url->setPath(substr($path, 0, $qpos));
		}

		$remoteAddr = NULL;
		$remoteHost = NULL;

		// Use real client address and host if trusted proxy is used
		$usingTrustedProxy = $remoteAddr && array_filter($this->proxies, function ($proxy) use ($remoteAddr) {
				return Http\Helpers::ipMatch($remoteAddr, $proxy);
			});

		if ($usingTrustedProxy) {
			if (!empty($headers['http_forwarded'])) {
				$forwardParams = preg_split('/[,;]/', $headers['http_forwarded']);

				foreach ($forwardParams as $forwardParam) {
					list($key, $value) = explode('=', $forwardParam, 2) + [1 => NULL];
					$proxyParams[strtolower(trim($key))][] = trim($value, " \t\"");
				}

				if (isset($proxyParams['for'])) {
					$address = $proxyParams['for'][0];

					//IPv4
					if (strpos($address, '[') === FALSE) {
						$remoteAddr = explode(':', $address)[0];

						//IPv6
					} else {
						$remoteAddr = substr($address, 1, strpos($address, ']') - 1);
					}
				}

				if (isset($proxyParams['host']) && count($proxyParams['host']) === 1) {
					$host = $proxyParams['host'][0];
					$startingDelimiterPosition = strpos($host, '[');

					//IPv4
					if ($startingDelimiterPosition === FALSE) {
						$remoteHostArr = explode(':', $host);
						$remoteHost = $remoteHostArr[0];

						if (isset($remoteHostArr[1])) {
							$url->setPort((int) $remoteHostArr[1]);
						}

						//IPv6
					} else {
						$endingDelimiterPosition = strpos($host, ']');
						$remoteHost = substr($host, strpos($host, '[') + 1, $endingDelimiterPosition - 1);
						$remoteHostArr = explode(':', substr($host, $endingDelimiterPosition));

						if (isset($remoteHostArr[1])) {
							$url->setPort((int) $remoteHostArr[1]);
						}
					}
				}

				$scheme = (isset($proxyParams['proto']) && count($proxyParams['proto']) === 1) ? $proxyParams['proto'][0] : 'http';

				$url->setScheme(strcasecmp($scheme, 'https') === 0 ? 'https' : 'http');

			} else {
				if (!empty($headers['http_x_forwarded_proto'])) {
					$url->setScheme(strcasecmp($headers['http_x_forwarded_proto'], 'https') === 0 ? 'https' : 'http');
				}

				if (!empty($headers['http_x_forwarded_port'])) {
					$url->setPort((int) $headers['http_x_forwarded_port']);
				}

				if (!empty($headers['http_x_forwarded_for'])) {
					$xForwardedForWithoutProxies = array_filter(explode(',', $headers['http_x_forwarded_for']), function ($ip) {
						return !array_filter($this->proxies, function ($proxy) use ($ip) {
							return Http\Helpers::ipMatch(trim($ip), $proxy);
						});
					});

					$remoteAddr = trim(end($xForwardedForWithoutProxies));
					$xForwardedForRealIpKey = key($xForwardedForWithoutProxies);
				}

				if (isset($xForwardedForRealIpKey) && !empty($headers['http_x_forwarded_host'])) {
					$xForwardedHost = explode(',', $headers['http_x_forwarded_host']);
					if (isset($xForwardedHost[$xForwardedForRealIpKey])) {
						$remoteHost = trim($xForwardedHost[$xForwardedForRealIpKey]);
					}
				}
			}
		}

		// COOKIE
		$cookies = isset($headers['cookie']) ? $this->parseCookie($headers['cookie']) : ['cookies' => []];
		$cookies = $cookies['cookies'];

		// remove invalid characters
		$reChars = '#^[' . self::CHARS . ']*+\z#u';

		if (!$this->binary) {
			$list = [&$cookies];

			foreach ($list as $key=>$val) {
				foreach ($val as $k => $v) {
					if (is_string($k) && (!preg_match($reChars, $k) || preg_last_error())) {
						unset($list[$key][$k]);

					} elseif (is_array($v)) {
						$list[$key][$k] = $v;
						$list[] = &$list[$key][$k];

					} else {
						$list[$key][$k] = (string) preg_replace('#[^' . self::CHARS . ']+#u', '', $v);
					}
				}
			}

			unset($list, $key, $val, $k, $v);
		}

		$request = new Request($url, NULL, NULL, NULL, $cookies, $headers, $method, $remoteAddr, $remoteHost, function () use ($rawBody) {
			return $rawBody;
		});

		$request->setProtocolVersion($httpVersion);

		return $request;
	}

	/**
	 * Determine if the message has been buffered as per the HTTP specification
	 *
	 * @param  string $message
	 *
	 * @return bool
	 */
	private function isEom($message) : bool
	{
		return (boolean) strpos($message, static::EOM);
	}

	/**
	 * @param string $cookie
	 *
	 * @return array
	 */
	private function parseCookie(string $cookie) : array
	{
		// Explode the cookie string using a series of semicolons
		$pieces = array_filter(array_map('trim', explode(';', $cookie)));

		// The name of the cookie (first kvp) must include an equal sign.
		if (empty($pieces) || !strpos($pieces[0], '=')) {
			return [];
		}

		// Create the default return array
		$data = array_merge(array_fill_keys(array_keys(self::$cookieParts), NULL), [
			'cookies'   => [],
			'data'      => [],
			'path'      => NULL,
			'http_only' => FALSE,
			'discard'   => FALSE,
			'domain'    => NULL,
		]);

		$foundNonCookies = 0;

		// Add the cookie pieces into the parsed data array
		foreach ($pieces as $part) {

			$cookieParts = explode('=', $part, 2);
			$key = trim($cookieParts[0]);

			if (count($cookieParts) == 1) {
				// Can be a single value (e.g. secure, httpOnly)
				$value = TRUE;

			} else {
				// Be sure to strip wrapping quotes
				$value = trim($cookieParts[1], " \n\r\t\0\x0B\"");
			}

			// Only check for non-cookies when cookies have been found
			if (!empty($data['cookies'])) {
				foreach (self::$cookieParts as $mapValue => $search) {
					if (!strcasecmp($search, $key)) {
						$data[$mapValue] = $mapValue == 'port' ? array_map('trim', explode(',', $value)) : $value;
						$foundNonCookies++;
						continue 2;
					}
				}
			}

			// If cookies have not yet been retrieved, or this value was not found in the pieces array, treat it as a
			// cookie. IF non-cookies have been parsed, then this isn't a cookie, it's cookie data. Cookies then data.
			$data[$foundNonCookies ? 'data' : 'cookies'][$key] = $value;
		}

		// Calculate the expires date
		if (!$data['expires'] && $data['max_age']) {
			$data['expires'] = time() + (int) $data['max_age'];
		}

		// Check path attribute according RFC6265 http://tools.ietf.org/search/rfc6265#section-5.2.4
		// "If the attribute-value is empty or if the first character of the
		// attribute-value is not %x2F ("/"):
		//   Let cookie-path be the default-path.
		// Otherwise:
		//   Let cookie-path be the attribute-value."
		if (!$data['path'] || substr($data['path'], 0, 1) !== '/') {
			$data['path'] = '/';
		}

		return $data;
	}
}
