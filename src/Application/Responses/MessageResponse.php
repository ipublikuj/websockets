<?php declare(strict_types = 1);

namespace IPub\WebSockets\Application\Responses;

use Nette;

/**
 * Simple data response only for own handled message
 *
 * @package        iPublikuj:WebSockets!
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

	/** @var mixed[] */
	private $data;

	/**
	 * @param mixed[] $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function create(): ?array
	{
		return $this->data;
	}

	/**
	 * @return string
	 *
	 * @throws Nette\Utils\JsonException
	 */
	public function __toString()
	{
		return Nette\Utils\Json::encode($this->create());
	}

}
