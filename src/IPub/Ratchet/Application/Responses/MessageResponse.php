<?php
/**
 * MessageResponse.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Responses
 * @since          1.0.0
 *
 * @date           14.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Application\Responses;

use Nette;

/**
 * Simple data response only for own handled message
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Responses
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @author         Vít Ledvinka, frosty22 <ledvinka.vit@gmail.com>
 */
class MessageResponse implements IResponse
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var string
	 */
	private $data;

	/**
	 * @param string $data
	 */
	public function __construct(string $data)
	{
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function create() : string
	{
		return $this->data;
	}
}
