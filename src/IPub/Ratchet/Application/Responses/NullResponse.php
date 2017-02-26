<?php
/**
 * NullResponse.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Responses
 * @since          1.0.0
 *
 * @date           25.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application\Responses;

use Nette;

/**
 * Null response
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Responses
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class NullResponse implements IResponse
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @return string
	 */
	public function create() : string
	{
		return NULL;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->create();
	}
}
