<?php
/**
 * Provider.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Message
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Message;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Entities;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => controller
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Message
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Provider extends Application\Application
{
	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Entities\Clients\IClient $from, string $message)
	{
		$request = $from->getRequest();

		$url = $request->getUrl(TRUE);

		try {
			/** @var \stdClass $data */
			$data = Utils\Json::decode($message);

			// Override route if is set in message
			if (isset($data->route)) {
				$url->setPath(rtrim($url->getPath(), '/') . '/' . ltrim($data->route, '/'));

				// Override data
				$message = $data->data;
			}

		} catch (Utils\JsonException $ex) {
			// Nothing to do here
		}

		$query = $url->getQuery();
		$query->add('action', 'message');

		$url->setQuery($query);

		$request->setUrl($url);

		$from->setRequest($request);

		$this->printer->success(sprintf('New message was recieved from %s', $from->getId()));

		$response = $this->processMessage($from, [
			'data' => $message,
		]);

		if (!$response instanceof Application\Responses\NullResponse) {
			/** @var Entities\Clients\IClient $client */
			foreach ($this->clientsStorage as $client) {
				$client->send($response);
			}
		}
	}
}
