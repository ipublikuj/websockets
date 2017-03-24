<?php
/**
 * ProtocolProxy.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols;

use Nette;

use IPub;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;

/**
 * Manage the various protocols of the WebSocket protocol
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class ProtocolProxy
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * Storage of enabled protocols
	 *
	 * @var IProtocol[]
	 */
	private $protocols = [];

	/**
	 * Get the protocol negotiator for the request, if supported
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return IProtocol
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function getProtocol(Http\IRequest $httpRequest) : IProtocol
	{
		foreach ($this->protocols as $protocol) {
			if ($protocol->isVersion($httpRequest)) {
				return $protocol;
			}
		}

		throw new Exceptions\InvalidArgumentException('Version not found');
	}

	/**
	 * @param Http\IRequest $httpRequest
	 *
	 * @return bool
	 */
	public function isProtocolEnabled(Http\IRequest $httpRequest) : bool
	{
		foreach ($this->protocols as $protocol) {
			if ($protocol->isVersion($httpRequest)) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Enable support for a specific version of the WebSocket protocol
	 *
	 * @param IProtocol $protocol
	 *
	 * @return void
	 */
	public function enableProtocol(IProtocol $protocol)
	{
		$this->protocols[$protocol->getVersion()] = $protocol;
	}

	/**
	 * Disable support for a specific WebSocket protocol
	 *
	 * @param string $protocolId The version ID to un-support
	 *
	 * @return void
	 */
	public function disableProtocol(string $protocolId)
	{
		unset($this->protocols[$protocolId]);
	}

	/**
	 * Get a string of protocols supported (comma separated)
	 *
	 * @return string
	 */
	public function getSupportedProtocols() : string
	{
		return implode(',', array_keys($this->protocols));
	}
}
