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
	private $app;

	/**
	 * @var SessionFactory
	 */
	private $sessionFactory;

	/**
	 * @param MessageComponentInterface $app
	 * @param SessionFactory $sessionFactory
	 */
	public function __construct(
		MessageComponentInterface $app,
		SessionFactory $sessionFactory
	) {
		$this->app = $app;
		$this->sessionFactory = $sessionFactory;
	}

	/**
	 * {@inheritdoc}
	 */
	public function onOpen(ConnectionInterface $conn)
	{
		$conn->session = $this->sessionFactory->create($conn);
		$conn->session->start();

		return $this->app->onOpen($conn);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onMessage(ConnectionInterface $from, $msg)
	{
		return $this->app->onMessage($from, $msg);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onClose(ConnectionInterface $conn)
	{
		$conn->session->close();

		return $this->app->onClose($conn);
	}

	/**
	 * {@inheritdoc}
	 */
	public function onError(ConnectionInterface $conn, \Exception $e)
	{
		return $this->app->onError($conn, $e);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubProtocols()
	{
		if ($this->app instanceof WebSocket\WsServerInterface) {
			return $this->app->getSubProtocols();

		} else {
			return [];
		}
	}

}
