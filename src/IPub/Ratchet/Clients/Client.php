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
 * @author         Vít Ledvinka, frosty22 <ledvinka.vit@gmail.com>
 */
class Client
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
	 * @return int
	 */
	public function getId() : int
	{
		return $this->connection->resourceId;
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function addParameter(string $key, $value)
	{
		$this->parameters->offsetSet($key, $value);
	}

	/**
	 * @param string $key
	 * @param mixed|NULL $default
	 *
	 * @return mixed|NULL
	 */
	public function getParameter(string $key, $default = NULL)
	{
		return $this->parameters->offsetExists($key) ? $this->parameters->offsetGet($key) : $default;
	}

	/**
	 * @param int|NULL $code
	 *
	 * @return void
	 */
	public function close(int $code = NULL)
	{
		$this->connection->close($code);
	}

	/**
	 * @param Responses\IResponse|string $response
	 *
	 * @return void
	 */
	public function send($response)
	{
		if ($response instanceof Responses\IResponse) {
			$response = $response->create();
		}

		$this->connection->send((string) $response);
	}

	/**
	 * @param NS\User $user
	 *
	 * @return void
	 */
	public function setUser(NS\User $user)
	{
		$this->user = $user;
	}

	/**
	 * @return NS\User|NULL
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param Message\RequestInterface $request
	 *
	 * @return void
	 */
	public function setRequest(Message\RequestInterface $request)
	{
		$this->connection->WebSocket->request = $request;
	}

	/**
	 * @return Message\RequestInterface
	 */
	public function getRequest() : Message\RequestInterface
	{
		return $this->connection->WebSocket->request;
	}
}
