<?php
/**
 * Topic.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\WAMP\V1;

use Nette;
use Nette\Utils;

use IPub;
use IPub\Ratchet\Application;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Clients;
use IPub\Ratchet\Exceptions;

/**
 * A topic/channel containing connections that have subscribed to it
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     WAMP
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface ITopic extends \IteratorAggregate, \Countable
{
	/**
	 * @return string
	 */
	function getId() : string;

	/**
	 * @return string
	 */
	function __toString();

	/**
	 * Send a message to all the connections in this topic
	 *
	 * @param string|Responses\IResponse $msg Payload to publish
	 * @param array $exclude                  A list of session IDs the message should be excluded from (blacklist)
	 * @param array $eligible                 A list of session Ids the message should be send to (whitelist)
	 *
	 * @return void
	 */
	function broadcast($msg, array $exclude = [], array $eligible = []);

	/**
	 * @param  Clients\Client $client
	 *
	 * @return bool
	 */
	function has(Clients\Client $client) : bool;

	/**
	 * @param Clients\Client $client
	 *
	 * @return void
	 */
	function add(Clients\Client $client);

	/**
	 * @param Clients\Client $client
	 *
	 * @return void
	 */
	function remove(Clients\Client $client);

	/**
	 * @return void
	 */
	function enableAutoDelete();

	/**
	 * @return void
	 */
	function disableAutoDelete();

	/**
	 * @return bool
	 */
	function isAutoDeleteEnabled() : bool;
}
