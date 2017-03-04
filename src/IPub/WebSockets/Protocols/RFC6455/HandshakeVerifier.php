<?php
/**
 * HandshakeVerifier.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols\RFC6455;

use Nette;

use IPub;
use IPub\WebSockets\Http;

/**
 * These are checks to ensure the client requested handshake are valid
 * Verification rules come from section 4.2.1 of the RFC6455 document
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @todo           Currently just returning invalid - should consider returning appropriate HTTP status code error #s
 */
final class HandshakeVerifier
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var int
	 */
	private $version;

	/**
	 * @param int $version
	 */
	public function __construct(int $version)
	{
		$this->version = $version;
	}

	/**
	 * Given an array of the headers this method will run through all verification methods
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return bool TRUE if all headers are valid, FALSE if 1 or more were invalid
	 */
	public function verifyAll(Http\IRequest $httpRequest)
	{
		$passes = 0;

		$passes += (int) $this->verifyMethod($httpRequest->getMethod());
		$passes += (int) $this->verifyHTTPVersion($httpRequest->getProtocolVersion());
		$passes += (int) $this->verifyRequestURI($httpRequest->getUrl()->getPath());
		$passes += (int) $this->verifyHost((string) $httpRequest->getHeader('Host'));
		$passes += (int) $this->verifyUpgradeRequest((string) $httpRequest->getHeader('Upgrade'));
		$passes += (int) $this->verifyConnection((string) $httpRequest->getHeader('Connection'));
		$passes += (int) $this->verifyKey((string) $httpRequest->getHeader('Sec-WebSocket-Key'));
		$passes += (int) $this->verifyVersion((int) $httpRequest->getHeader('Sec-WebSocket-Version')); // Temporarily breaking functionality

		return $passes === 8;
	}

	/**
	 * Test the HTTP method MUST be "GET"
	 *
	 * @param string $val
	 *
	 * @return bool
	 */
	public function verifyMethod(string $val) : bool
	{
		return strtolower($val) === 'get';
	}

	/**
	 * Test the HTTP version passed. MUST be 1.1 or greater
	 *
	 * @param float|NULL $val
	 *
	 * @return bool
	 */
	public function verifyHTTPVersion(float $val = NULL) : bool
	{
		return $val && $val >= 1.1;
	}

	/**
	 * @param string $val
	 *
	 * @return bool
	 */
	public function verifyRequestURI(string $val) : bool
	{
		if ($val[0] != '/') {
			return FALSE;
		}

		if (strstr($val, '#') !== FALSE) {
			return FALSE;
		}

		if (!extension_loaded('mbstring')) {
			return TRUE;
		}

		return mb_check_encoding($val, 'US-ASCII');
	}

	/**
	 * @param string|NULL $val
	 *
	 * @return bool
	 *
	 * @todo Find out if I can find the master socket, ensure the port is attached to header if not 80 or 443 - not sure if this is possible, as I tried to hide it
	 * @todo Once I fix HTTP::getHeaders just verify this isn't NULL or empty...or maybe need to verify it's a valid domain??? Or should it equal $_SERVER['HOST'] ?
	 */
	public function verifyHost(string $val = NULL) : bool
	{
		return $val !== NULL;
	}

	/**
	 * Verify the Upgrade request to WebSockets
	 *
	 * @param string $val MUST equal "websocket"
	 *
	 * @return bool
	 */
	public function verifyUpgradeRequest(string $val) : bool
	{
		return strtolower($val) === 'websocket';
	}

	/**
	 * Verify the Connection header
	 *
	 * @param string $val MUST equal "Upgrade"
	 *
	 * @return bool
	 */
	public function verifyConnection(string $val) : bool
	{
		$val = strtolower($val);

		if ($val === 'upgrade') {
			return TRUE;
		}

		$values = explode(',', str_replace(', ', ',', $val));

		return array_search('upgrade', $values) !== FALSE;
	}

	/**
	 * This function verifies the nonce is valid (64 big encoded, 16 bytes random string)
	 *
	 * @param string|null $val
	 *
	 * @return bool
	 *
	 * @todo  The spec says we don't need to base64_decode - can I just check if the length is 24 and not decode?
	 * @todo  Check the spec to see what the encoding of the key could be
	 */
	public function verifyKey(string $val = NULL) : bool
	{
		return strlen(base64_decode($val)) === 16;
	}

	/**
	 * Verify the version passed matches this RFC
	 *
	 * @param string|int $val MUST equal to given version
	 *
	 * @return bool
	 */
	public function verifyVersion(int $val) : bool
	{
		return $val === $this->version;
	}

	/**
	 * @todo Write logic for this method.  See section 4.2.1.8
	 */
	public function verifyProtocol($val) : bool
	{

	}

	/**
	 * @todo Write logic for this method.  See section 4.2.1.9
	 */
	public function verifyExtensions($val) : bool
	{

	}
}
