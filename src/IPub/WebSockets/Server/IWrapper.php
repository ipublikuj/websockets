<?php
/**
 * IWrapper.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           04.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use IPub;
use IPub\WebSockets\Entities;

interface IWrapper
{
	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	function onOpen(Entities\Clients\IClient $client);

	/**
	 * @param Entities\Clients\IClient $client
	 * @param string $message
	 *
	 * @return void
	 */
	function onMessage(Entities\Clients\IClient $client, string $message);

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	function onClose(Entities\Clients\IClient $client);

	/**
	 * @param Entities\Clients\IClient $client
	 * @param \Exception $ex
	 *
	 * @return void
	 */
	function onError(Entities\Clients\IClient $client, \Exception $ex);
}
