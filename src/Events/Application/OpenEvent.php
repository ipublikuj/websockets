<?php declare(strict_types = 1);

/**
 * OpenEvent.php
 *
 * @copyright      More in LICENSE.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           15.11.19
 */

namespace IPub\WebSockets\Events\Application;

use IPub\WebSockets\Application;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;
use Symfony\Contracts\EventDispatcher;

/**
 * Connection open event
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class OpenEvent extends EventDispatcher\Event
{

	/** @var Application\IApplication */
	private $application;

	/** @var Entities\Clients\IClient */
	private $client;

	/** @var Http\IRequest */
	private $httpRequest;

	/**
	 * @param Application\IApplication $application
	 * @param Entities\Clients\IClient $client
	 * @param Http\IRequest $httpRequest
	 */
	public function __construct(
		Application\IApplication $application,
		Entities\Clients\IClient $client,
		Http\IRequest $httpRequest
	) {
		$this->application = $application;
		$this->client = $client;
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @return Application\IApplication
	 */
	public function getApplication(): Application\IApplication
	{
		return $this->application;
	}

	/**
	 * @return Entities\Clients\IClient
	 */
	public function getClient(): Entities\Clients\IClient
	{
		return $this->client;
	}

	/**
	 * @return Http\IRequest
	 */
	public function getHttpRequest(): Http\IRequest
	{
		return $this->httpRequest;
	}

}
