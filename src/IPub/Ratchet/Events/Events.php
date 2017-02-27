<?php
/**
 * Events.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Events
 * @since          1.0.0
 *
 * @date           27.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Events;

/**
 * Extension events definitions
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Events
{
	const SERVER_LAUNCHED = 'ipub.ratchet.server.launched';

	const CLIENT_CONNECTED = 'ipub.ratchet.client.connected';
	const CLIENT_DISCONNECTED = 'ipub.ratchet.client.disconnected';
	const CLIENT_ERROR = 'ipub.ratchet.client.error';
	const CLIENT_REJECTED = 'ipub.ratchet.client.rejected';

	const PUSHER_FAIL = 'ipub.ratchet.push.fail';
	const PUSHER_SUCCESS = 'ipub.ratchet.push.success';
}
