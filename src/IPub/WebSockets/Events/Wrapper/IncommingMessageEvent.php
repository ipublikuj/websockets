<?php
/**
 * IncommingMessageEvent.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           15.11.19
 */

namespace IPub\WebSockets\Events\Wrapper;

use Symfony\Contracts\EventDispatcher;

use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;

/**
 * Incomming message event
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Events
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class IncommingMessageEvent extends EventDispatcher\Event
{
	/**
	 * @var Entities\Clients\IClient
	 */
	private $client;

	/**
	 * @var Http\IRequest
	 */
	private $httpRequest;

	/**
	 * @var string
	 */
	private $message;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param Http\IRequest $httpRequest
	 * @param string $message
	 */
	public function __construct(
		Entities\Clients\IClient $client,
		Http\IRequest $httpRequest,
		string $message
	) {
		$this->client = $client;
		$this->httpRequest = $httpRequest;
		$this->message = $message;
	}

	/**
	 * @return Entities\Clients\IClient
	 */
	public function getClient() : Entities\Clients\IClient
	{
		return $this->client;
	}

	/**
	 * @return Http\IRequest
	 */
	public function getHttpRequest() : Http\IRequest
	{
		return $this->httpRequest;
	}

	/**
	 * @return string
	 */
	public function getMessage() : string
	{
		return $this->message;
	}
}
