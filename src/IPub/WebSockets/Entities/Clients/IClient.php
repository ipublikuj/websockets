<?php
/**
 * IClient.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Entities\Clients;

use Nette\Security as NS;

use React\Socket;

use IPub\WebSockets\Application\Responses;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;

/**
 * Single client connection interface
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IClient
{
	/**
	 * @return int
	 */
	function getId() : int;

	/**
	 * @return Socket\ConnectionInterface
	 */
	function getConnection() : Socket\ConnectionInterface;

	/**
	 * @param bool $state
	 *
	 * @return void
	 */
	function setHTTPHeadersReceived(bool $state);

	/**
	 * @return bool
	 */
	function isHTTPHeadersReceived() : bool;

	/**
	 * @param string $buffer
	 *
	 * @return void
	 */
	function setHttpBuffer(string $buffer);

	/**
	 * @return string
	 */
	function getHttpBuffer() : string;

	/**
	 * @param Http\IRequest $httpRequest
	 *
	 * @return void
	 */
	function setRequest(Http\IRequest $httpRequest);

	/**
	 * @return Http\IRequest
	 */
	function getRequest() : Http\IRequest;

	/**
	 * @param Entities\WebSockets\IWebSocket $webSocket
	 *
	 * @return void
	 */
	function setWebSocket(Entities\WebSockets\IWebSocket $webSocket);

	/**
	 * @return Entities\WebSockets\IWebSocket
	 */
	function getWebSocket() : Entities\WebSockets\IWebSocket;

	/**
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return void
	 */
	function addParameter(string $key, $value);

	/**
	 * @param string $key
	 * @param mixed|NULL $default
	 *
	 * @return mixed|NULL
	 */
	function getParameter(string $key, $default = NULL);

	/**
	 * @param int|NULL $code
	 *
	 * @return void
	 */
	function close(?int $code = NULL);

	/**
	 * @param Responses\IResponse|string $response
	 *
	 * @return void
	 */
	function send($response);

	/**
	 * @param NS\User $user
	 *
	 * @return void
	 */
	function setUser(NS\User $user);

	/**
	 * @return NS\User|NULL
	 */
	function getUser();
}
