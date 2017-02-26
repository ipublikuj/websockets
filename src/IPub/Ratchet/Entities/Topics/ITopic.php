<?php
/**
 * Topic.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Entities\Topics;

use IPub;
use IPub\Ratchet\Application\Responses;
use IPub\Ratchet\Entities;

/**
 * A topic/channel containing connections that have subscribed to it
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Entities
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
	 * @param  Entities\Clients\IClient $client
	 *
	 * @return bool
	 */
	function has(Entities\Clients\IClient $client) : bool;

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	function add(Entities\Clients\IClient $client);

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	function remove(Entities\Clients\IClient $client);

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
