<?php
/**
 * Provider.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 * @since          1.0.0
 *
 * @date           19.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Session;

use Nette;
use Nette\Http;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket;

/**
 * This component will allow access to session data from your Nette Framework website for each user connected
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Provider implements MessageComponentInterface, WebSocket\WsServerInterface
{
	/**
	 * @var MessageComponentInterface
	 */
	private $application;

	/**
	 * @var Http\Session|SwitchableSession
	 */
	private $session;

	/**
	 * @var Nette\Security\User
	 */
	private $user;

	/**
	 * @param MessageComponentInterface $application
	 * @param Http\Session $session
	 * @param Nette\Security\User $user
	 */
	public function __construct(
		MessageComponentInterface $application,
		Http\Session $session,
		Nette\Security\User $user
	) {
		$this->application = $application;
		$this->session = $session;
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(ConnectionInterface $conn)
	{
		$conn->user = clone $this->user;

		return $this->application->onOpen($conn);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(ConnectionInterface $from, $msg)
	{
		$this->session->attach($from);

		if (!$this->session->isStarted()) {
			$this->session->start();
		}

		return $this->application->onMessage($from, $msg);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(ConnectionInterface $conn)
	{
		$this->session->detach($conn);

		return $this->application->onClose($conn);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		return $this->application->onError($conn, $e);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubProtocols()
	{
		if ($this->application instanceof WebSocket\WsServerInterface) {
			return $this->application->getSubProtocols();

		} else {
			return [];
		}
	}

}
