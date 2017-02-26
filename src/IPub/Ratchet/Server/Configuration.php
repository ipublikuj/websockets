<?php
/**
 * Configuration.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Server;

use Nette;

/**
 * Ratchet server configuration container
 *
 * @package        iPublikuj:Ratchet!
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
	 * @param string $httpHost
	 * @param int $port
	 * @param string $address
	 */
	public function __construct(string $httpHost = 'localhost', int $port = 8080, string $address = '0.0.0.0')
	{
		$this->httpHost = $httpHost;
		$this->port = $port;
		$this->address = $address;
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
}
