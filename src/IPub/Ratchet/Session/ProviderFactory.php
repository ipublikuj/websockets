<?php
/**
 * ProviderFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 * @since          1.0.0
 *
 * @date           23.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Session;

use Ratchet\MessageComponentInterface;

/**
 * Session component provider factory
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Session
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface ProviderFactory
{
	/**
	 * @param MessageComponentInterface $application
	 *
	 * @return Provider
	 */
	function create(MessageComponentInterface $application) : Provider;
}
