<?php
/**
 * Server.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Server;

use Nette;

use Ratchet;

use React;
use React\EventLoop\LoopInterface;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Router;
use IPub\Ratchet\Session;

/**
 * Ratchet server for Nette - run instead of Nette application
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Server
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $httpHost;

	/**
	 * @var int
	 */
	private $port;

	/**
	 * @var LoopInterface
	 */
	private $loop;

	/**
	 * @var Ratchet\Server\IoServer
	 */
	private $server;

	/**
	 * @var Ratchet\Server\IoServer
	 */
	private $flashServer;

	/**
	 * @param Ratchet\MessageComponentInterface|Ratchet\Wamp\WampServerInterface $application
	 * @param LoopInterface $loop
	 * @param string $httpHost
	 * @param int $port
	 * @param string $address
	 * @param bool $useSession
	 * @param Session\ProviderFactory $providerFactory
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function __construct(
		$application,
		LoopInterface $loop,
		string $httpHost = 'localhost',
		int $port = 8080,
		string $address = '127.0.0.1',
		bool $useSession = FALSE,
		Session\ProviderFactory $providerFactory
	) {
		$this->loop = $loop;

		$this->httpHost = $httpHost;
		$this->port = $port;

		$socket = new React\Socket\Server($this->loop);
		$socket->listen($port, $address);

		if ($application instanceof Ratchet\MessageComponentInterface) {
			if ($useSession) {
				$component = new Ratchet\WebSocket\WsServer($providerFactory->create($application));

			} else {
				$component = new Ratchet\WebSocket\WsServer($application);
			}

		} elseif ($application instanceof Ratchet\Wamp\WampServerInterface) {
			if ($useSession) {
				$component = new Ratchet\WebSocket\WsServer($providerFactory->create(new Ratchet\Wamp\WampServer($application)));

			} else {
				$component = new Ratchet\WebSocket\WsServer(new Ratchet\Wamp\WampServer($application));
			}

		} else {
			throw new Exceptions\InvalidArgumentException('Invalid application provided to Ratchet server.');
		}

		$this->server = new Ratchet\Server\IoServer(
			new Ratchet\Http\HttpServer($component),
			$socket,
			$this->loop
		);

		$policy = new Ratchet\Server\FlashPolicy;
		$policy->addAllowedAccess($httpHost, 80);
		$policy->addAllowedAccess($httpHost, $port);

		$flashSock = new React\Socket\Server($this->loop);

		$this->flashServer = new Ratchet\Server\IoServer($policy, $flashSock);

		if ($port === 80) {
			$flashSock->listen(843, '0.0.0.0');

		} else {
			$flashSock->listen(8843);
		}
	}

	/**
	 * Run IO server
	 * 
	 * @return void
	 */
	public function run()
	{
		$this->server->run();
	}
}
