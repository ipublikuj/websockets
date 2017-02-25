<?php
/**
 * WrapperFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Server;

use IPub;
use IPub\Ratchet\Application;

/**
 * Ratchet server wrapper factory
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface WrapperFactory
{
	/**
	 * @param Application\IApplication $application
	 *
	 * @return Wrapper
	 */
	function create(Application\IApplication $application) : Wrapper;
}
