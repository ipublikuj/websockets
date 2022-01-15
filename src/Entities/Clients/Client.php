<?php declare(strict_types = 1);

namespace IPub\WebSockets\Entities\Clients;

use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;
use Nette;
use Nette\Security as NS;
use Nette\Utils;
use React\Socket;

/**
 * Single client connection
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Client implements IClient
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/** @var Socket\ConnectionInterface */
	private $connection;

	/** @var NS\User|null */
	private $user;

	/** @var int */
	private $id;

	/** @var bool */
	private $httpHeadersReceived = false;

	/** @var string */
	private $httpBuffer = '';

	/** @var string|null */
	private $remoteAddress;

	/** @var Http\IRequest */
	private $httpRequest;

	/** @var Entities\WebSockets\IWebSocket */
	private $webSocket;

	/** @var Utils\ArrayHash */
	private $parameters;

	/**
	 * @param int $id
	 * @param Socket\ConnectionInterface $connection
	 */
	public function __construct(int $id, Socket\ConnectionInterface $connection)
	{
		$this->connection = $connection;

		$this->id = $id;
		$this->remoteAddress = $connection->getRemoteAddress();

		$this->parameters = new Utils\ArrayHash();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getConnection(): Socket\ConnectionInterface
	{
		return $this->connection;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHttpHeadersReceived(bool $state): void
	{
		$this->httpHeadersReceived = $state;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isHttpHeadersReceived(): bool
	{
		return $this->httpHeadersReceived;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setHttpBuffer(string $buffer): void
	{
		$this->httpBuffer = $buffer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHttpBuffer(): string
	{
		return $this->httpBuffer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRequest(Http\IRequest $httpRequest): void
	{
		$this->httpRequest = $httpRequest;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRequest(): Http\IRequest
	{
		return clone $this->httpRequest;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setWebSocket(Entities\WebSockets\IWebSocket $webSocket): void
	{
		$this->webSocket = $webSocket;
	}

	/**
	 * @return Entities\WebSockets\IWebSocket
	 */
	public function getWebSocket(): Entities\WebSockets\IWebSocket
	{
		if ($this->webSocket === null) {
			throw new Exceptions\InvalidStateException('Socket is not defined');
		}

		return $this->webSocket;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addParameter(string $key, $value): void
	{
		$this->parameters->offsetSet($key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameter(string $key, $default = null)
	{
		return $this->parameters->offsetExists($key) ? $this->parameters->offsetGet($key) : $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close(?int $code = null): void
	{
		$this->webSocket->getProtocol()->close($this, $code);
	}

	/**
	 * {@inheritdoc}
	 */
	public function send($response): void
	{
		if ($response instanceof Responses\IResponse) {
			$response = (string) $response;
		}

		$this->webSocket->getProtocol()->send($this, $response);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setUser(NS\User $user): void
	{
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUser(): ?NS\User
	{
		return $this->user;
	}

}
