<?php
/**
 * IClient.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Entities\Clients;

use Nette;
use Nette\Security as NS;
use Nette\Utils;

use Guzzle\Http\Message;

use IPub;
use IPub\Ratchet\Application\Responses;

/**
 * Single client connection interface
 *
 * @package        iPublikuj:Ratchet!
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
	function close(int $code = NULL);

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

	/**
	 * @param Message\RequestInterface $request
	 *
	 * @return void
	 */
	function setRequest(Message\RequestInterface $request);

	/**
	 * @return Message\RequestInterface
	 */
	function getRequest() : Message\RequestInterface;
}
