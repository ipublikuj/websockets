<?php
/**
 * Configuration.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use Nette;

/**
 * WebSockets server configuration container
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Configuration
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $httpHost;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var string
	 */
	private $address;

	/**
	 * @var bool
	 */
	private $enableSSL = FALSE;

	/**
	 * @var array
	 */
	private $sslSettings = [];

	/**
	 * @param string $httpHost
	 * @param int $port
	 * @param string $address
	 * @param bool $enableSSL
	 * @param array $sslSettings
	 */
	public function __construct(
		string $httpHost = 'localhost',
		int $port = 8080,
		string $address = '0.0.0.0',
		bool $enableSSL,
		array $sslSettings
	) {
		$this->httpHost = $httpHost;
		$this->port = $port;
		$this->address = $address;
		$this->enableSSL = $enableSSL;
		$this->sslSettings = $sslSettings;
	}

	/**
	 * @return string
	 */
	public function getHttpHost() : string
	{
		return $this->httpHost;
	}

	/**
	 * @return int
	 */
	public function getPort() : int
	{
		return $this->port;
	}

	/**
	 * @return string
	 */
	public function getAddress() : string
	{
		return $this->address;
	}

	/**
	 * @return bool
	 */
	public function isSSLEnabled() : bool
	{
		return $this->enableSSL;
	}

	/**
	 * @return array
	 */
	public function getSSLConfiguration() : array
	{
		return $this->sslSettings;
	}
}
