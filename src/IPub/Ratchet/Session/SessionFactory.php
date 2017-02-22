<?php
/**
 * SessionFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 * @since          1.0.0
 *
 * @date           21.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Session;

use Nette;
use Nette\Http;

use Ratchet\ConnectionInterface;

/**
 * WebSocket session factory
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class SessionFactory
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var \SessionHandler
	 */
	private $handler;

	/**
	 * @var Http\Session
	 */
	private $session;

	/**
	 * @param \SessionHandlerInterface|NULL $handler
	 * @param Http\Session $session
	 */
	public function __construct(\SessionHandlerInterface $handler = NULL, Http\Session $session)
	{
		$this->handler = $handler;
		$this->session = $session;
	}

	/**
	 * @param ConnectionInterface $connection
	 *
	 * @return Session
	 */
	public function create(ConnectionInterface $connection) : Session
	{
		return new Session($connection, $this->session->getName(), $this->handler);
	}
}
