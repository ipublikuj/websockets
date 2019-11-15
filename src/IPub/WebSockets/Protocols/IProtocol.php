<?php
/**
 * IProtocol.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols;

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
	public function getVersion() : int;

	/**
	 * Given an HTTP header, determine if this version should handle the protocol
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return bool
	 */
	public function isVersion(Http\IRequest $httpRequest) : bool;

	/**
	 * Perform the handshake and return the response headers
	 *
	 * @param Http\IRequest $httpRequest
	 *
	 * @return Http\IResponse
	 */
	public function doHandshake(Http\IRequest $httpRequest) : Http\IResponse;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param Application\IApplication $application
	 * @param string $message
	 *
	 * @return void
	 */
	public function handleMessage(Entities\Clients\IClient $client, Application\IApplication $application, string $message = '') : void;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param string|int|IData $payload
	 *
	 * @return void
	 */
	public function send(Entities\Clients\IClient $client, $payload) : void;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param int|NULL $code
	 *
	 * @return void
	 */
	public function close(Entities\Clients\IClient $client, ?int $code = NULL) : void;
}
