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
use Nette\Security as NS;

use Ratchet\WebSocket;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Clients;

/**
 * This component will allow access to session data from your Nette Framework website for each user connected
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Provider implements Application\IApplication, WebSocket\WsServerInterface
{
	/**
	 * @var Application\IApplication
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
	 * @param Application\IApplication $application
	 * @param Http\Session $session
	 * @param NS\User $user
	 */
	public function __construct(
		Application\IApplication $application,
		Http\Session $session,
		NS\User $user
	) {
		$this->application = $application;
		$this->session = $session;
		$this->user = $user;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(Clients\IClient $client)
	{
		$client->setUser(clone $this->user);

		return $this->application->onOpen($client);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Clients\IClient $from, string $message)
	{
		if ($this->session instanceof SwitchableSession) {
			$this->session->attach($from);

			if (!$this->session->isStarted()) {
				$this->session->start();
			}
		}

		return $this->application->onMessage($from, $message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(Clients\IClient $client)
	{
		if ($this->session instanceof SwitchableSession) {
			$this->session->detach();
		}

		return $this->application->onClose($client);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(Clients\IClient $client, \Exception $ex)
	{
		return $this->application->onError($client, $ex);
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
