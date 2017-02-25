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

use IPub;
use IPub\Ratchet\Application\UI;
use IPub\Ratchet\Clients;

/**
 * Application which run on server and provide creating controllers
 * with correctly params - convert message => controller
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Application
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class MessageApplication extends Application
{
	/**
	 * {@inheritdoc}
	 */
	public function onMessage(Clients\Client $from, $msg)
	{
		$request = $from->getRequest();

		$url = $request->getUrl(TRUE);

		try {
			/** @var \stdClass $data */
			$data = Utils\Json::decode($msg);

			// Override route if is set in message
			if (isset($data->route)) {
				$url->setPath(rtrim($url->getPath(), '/') . '/' . ltrim($data->route, '/'));

				// Override data
				$msg = $data->data;
			}

		} catch (Utils\JsonException $ex) {
			// Nothing to do here
		}

		$query = $url->getQuery();
		$query->add('action', 'message');

		$url->setQuery($query);

		$request->setUrl($url);

		$from->setRequest($request);

		parent::onMessage($from, [
			'data' => $msg,
		]);
	}
}
