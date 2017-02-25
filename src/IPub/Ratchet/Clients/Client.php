<?php
/**
 * Client.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Clients
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Clients;

use Nette;
use Nette\Security as NS;
use Nette\Utils;

use Guzzle\Http\Message;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Application\Responses;

/**
 * Single client connection (proxy class of ConnectionInterface)
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Clients
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Client implements IClient
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var ConnectionInterface
	 */
	private $connection;

	/**
	 * @var NS\User|NULL
	 */
	private $user;

	/**
	 * @var Utils\ArrayHash
	 */
	private $parameters;

	/**
	 * @param ConnectionInterface $connection
	 */
	public function __construct(ConnectionInterface $connection)
	{
		$this->connection = $connection;
		$this->parameters = new Utils\ArrayHash;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId() : int
	{
		return $this->connection->resourceId;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addParameter(string $key, $value)
	{
		$this->parameters->offsetSet($key, $value);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameter(string $key, $default = NULL)
	{
		return $this->parameters->offsetExists($key) ? $this->parameters->offsetGet($key) : $default;
	}

	/**
	 * {@inheritdoc}
	 */
	public function close(int $code = NULL)
	{
		$this->connection->close($code);
	}

	/**
	 * {@inheritdoc}
	 */
	public function send($response)
	{
		if ($response instanceof Responses\IResponse) {
			$response = $response->create();
		}

		$this->connection->send((string) $response);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setUser(NS\User $user)
	{
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRequest(Message\RequestInterface $request)
	{
		$this->connection->WebSocket->request = $request;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRequest() : Message\RequestInterface
	{
		return $this->connection->WebSocket->request;
	}
}
