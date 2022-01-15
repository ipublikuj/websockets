<?php declare(strict_types = 1);

namespace IPub\WebSockets\Entities\Clients;

use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;
use IPub\WebSockets\Protocols;
use Nette\Security as NS;
use React\Socket;

/**
 * Single client connection interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IClient
{

	/**
	 * @return int
	 */
	public function getId(): int;

	/**
	 * @return Socket\ConnectionInterface
	 */
	public function getConnection(): Socket\ConnectionInterface;

	/**
	 * @param bool $state
	 *
	 * @return void
	 */
	public function setHttpHeadersReceived(bool $state): void;

	/**
	 * @return bool
	 */
	public function isHttpHeadersReceived(): bool;

	/**
	 * @param string $buffer
	 *
	 * @return void
	 */
	public function setHttpBuffer(string $buffer): void;

	/**
	 * @return string
	 */
	public function getHttpBuffer(): string;

	/**
	 * @param Http\IRequest $httpRequest
	 *
	 * @return void
	 */
	public function setRequest(Http\IRequest $httpRequest): void;

	/**
	 * @return Http\IRequest
	 */
	public function getRequest(): Http\IRequest;

	/**
	 * @param Entities\WebSockets\IWebSocket $webSocket
	 *
	 * @return void
	 */
	public function setWebSocket(Entities\WebSockets\IWebSocket $webSocket): void;

	/**
	 * @return Entities\WebSockets\IWebSocket
	 */
	public function getWebSocket(): Entities\WebSockets\IWebSocket;

	/**
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function addParameter(string $key, $value): void;

	/**
	 * @param string $key
	 * @param mixed|null $default
	 *
	 * @return mixed|null
	 */
	public function getParameter(string $key, $default = null);

	/**
	 * @param int|null $code
	 *
	 * @return void
	 */
	public function close(?int $code = null): void;

	/**
	 * @param Responses\IResponse|Protocols\IData|string $response
	 *
	 * @return void
	 */
	public function send($response): void;

	/**
	 * @param NS\User $user
	 *
	 * @return void
	 */
	public function setUser(NS\User $user): void;

	/**
	 * @return NS\User|null
	 */
	public function getUser(): ?NS\User;

}
