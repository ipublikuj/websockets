<?php
/**
 * SwitchableUser.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Security
 * @since          1.0.0
 *
 * @date           23.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Security;

use Nette;
use Nette\Security as NS;

use Ratchet\ConnectionInterface;

use IPub;
use IPub\Ratchet\Exceptions;

/**
 * WebSocket application user switcher
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Security
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class SwitchableUser extends NS\User
{
	/**
	 * @var NS\User
	 */
	private $systemUser;

	/**
	 * @param NS\User $user
	 */
	public function __construct(NS\User $user)
	{
		$this->systemUser = $user;
	}
}
