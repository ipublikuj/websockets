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
use Nette\Utils;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\Wamp;

use Guzzle\Http\Message;

use IPub;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;

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
		// Import message data into route
		$url->getQuery()->add('data', $msg);

		try {
			/** @var \stdClass $data */
			$data = Utils\Json::decode($msg);

			// Override route if is set in message
			if (isset($data->route)) {
				$url->setPath('/' . ltrim($data->route, '/'));

				$url->getQuery()->remove('data');
				$url->getQuery()->add('data', $data->data);
			}

		} catch (Utils\JsonException $ex) {
			// Nothing to do here
		}

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
		$controller = $this->controllerFactory->createController($from, $controllerName);

		$controller->setDefaultAction('message');

		$response = $controller->run($appRequest);

		/** @var Clients\Client $connection */
		foreach ($this->clientsStorage as $connection) {
			$connection->send($response);
		}
	}
}
