<?php declare(strict_types = 1);

namespace IPub\WebSockets\Application\Responses;

use Nette;

/**
 * Null response
 *
 * @package        iPublikuj:WebSockets!
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
	 * {@inheritdoc}
	 */
	public function create(): ?array
	{
		return null;
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->create();
	}

}
