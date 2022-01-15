<?php declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use Nette;

/**
 * WebSockets server configuration container
 *
 * @package        iPublikuj:WebSockets!
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

	/** @var int */
	private $port;

	/** @var string */
	private $address;

	/** @var bool */
	private $enableSSL = false;

	/** @var array */
	private $sslSettings = [];

	/**
	 * @param int $port
	 * @param string $address
	 * @param bool $enableSSL
	 * @param array $sslSettings
	 */
	public function __construct(
		int $port = 8080,
		string $address = '0.0.0.0',
		bool $enableSSL = false,
		array $sslSettings = []
	) {
		$this->port = $port;
		$this->address = $address;
		$this->enableSSL = $enableSSL;
		$this->sslSettings = $sslSettings;
	}

	/**
	 * @param int $port
	 *
	 * @return void
	 */
	public function setPort(int $port): void
	{
		$this->port = $port;
	}

	/**
	 * @return int
	 */
	public function getPort(): int
	{
		return $this->port;
	}

	/**
	 * @param string $address
	 *
	 * @return void
	 */
	public function setAddress(string $address): void
	{
		$this->address = $address;
	}

	/**
	 * @return string
	 */
	public function getAddress(): string
	{
		return $this->address;
	}

	/**
	 * @return bool
	 */
	public function isSslEnabled(): bool
	{
		return $this->enableSSL;
	}

	/**
	 * @return array
	 */
	public function getSslConfiguration(): array
	{
		return $this->sslSettings;
	}

}
