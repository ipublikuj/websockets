<?php
/**
 * IProtocol.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols;

use IPub;
use IPub\WebSockets\Application;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;

/**
 * A standard interface for interacting with the various version of the WebSocket protocol
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IProtocol
{
	/**
	 * Although the version has a name associated with it the integer returned is the proper identification
	 *
	 * @return int
	 */
	function getVersion() : int;

	/**
	 * Given an HTTP header, determine if this version should handle the protocol
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return bool
	 */
	function isVersion(Http\IRequest $httpRequest) : bool;

	/**
	 * Perform the handshake and return the response headers
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return Http\IResponse
	 */
	function doHandshake(Http\IRequest $httpRequest) : Http\IResponse;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param Application\IApplication $application
	 * @param string $message
	 *
	 * @return void
	 */
	function handleMessage(Entities\Clients\IClient $client, Application\IApplication $application, string $message = '');

	/**
	 * @param Entities\Clients\IClient $client
	 * @param string|int|IData $payload
	 *
	 * @return void
	 */
	function send(Entities\Clients\IClient $client, $payload);

	/**
	 * @param Entities\Clients\IClient $client
	 * @param int|NULL $code
	 *
	 * @return void
	 */
	function close(Entities\Clients\IClient $client, int $code = NULL);
}
