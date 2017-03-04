<?php
/**
 * IResponse.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Responses
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Application\Responses;

/**
 * Response interface
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Responses
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @author         Vít Ledvinka, frosty22 <ledvinka.vit@gmail.com>
 */
interface IResponse
{
	/**
	 * @return string
	 */
	function create() : string;
}
