<?php
/**
 * MessageApplication.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application;

use Nette;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Wamp;

use Guzzle\Http\Message;

use IPub;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Exceptions;
use IPub\Ratchet\Storage;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => control.
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class MessageApplication extends Application implements MessageComponentInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function onMessage(ConnectionInterface $from, $msg)
	{
		/** @var Message\RequestInterface $request */
		$request = $from->WebSocket->request;

		$url = $request->getUrl(TRUE);
		$url->getQuery()->add('data', $msg);

		$request->setUrl($url);

		$appRequest = $this->router->match($request);

		if ($appRequest === NULL) {
			throw new Exceptions\BadRequestException('Invalid message - router cant create request.');
		}

		$controllerName = $appRequest->getControllerName();
		$controllerClass = $this->controllerFactory->getControllerClass($controllerName);

		if (!is_subclass_of($controllerClass, UI\IMessageController::class)) {
			throw new Exceptions\BadRequestException(sprintf('%s must be subclass of IPub\Ratchet\UI\MessageController.', $controllerClass));
		}

		/** @var UI\IMessageController $controller */
		$controller = $this->controllerFactory->createController($controllerName);

		$response = $controller->run($appRequest);

		/** @var IPub\Ratchet\Server\Connection $connection */
		foreach ($this->connectionStorage as $connection) {
			$connection->send($response);
		}
	}
}
