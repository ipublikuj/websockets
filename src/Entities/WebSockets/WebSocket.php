<?php declare(strict_types = 1);

namespace IPub\WebSockets\Entities\WebSockets;

use IPub\WebSockets\Protocols;
use Nette;

final class WebSocket implements IWebSocket
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/** @var bool */
	private $established = false;

	/** @var bool */
	private $closing = false;

	/** @var Protocols\IProtocol */
	private $protocol;

	/** @var Protocols\IMessage */
	private $message;

	/** @var Protocols\IFrame */
	private $frame;

	/**
	 * @param bool $established
	 * @param bool $closing
	 * @param Protocols\IProtocol $protocol
	 */
	public function __construct(bool $established, bool $closing, Protocols\IProtocol $protocol)
	{
		$this->established = $established;
		$this->closing = $closing;
		$this->protocol = $protocol;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setEstablished(bool $state): void
	{
		$this->established = $state;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isEstablished(): bool
	{
		return $this->established;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClosing(bool $state): void
	{
		$this->closing = $state;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isClosing(): bool
	{
		return $this->closing;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getProtocol(): Protocols\IProtocol
	{
		return $this->protocol;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setMessage(Protocols\IMessage $message): void
	{
		$this->message = $message;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMessage(): Protocols\IMessage
	{
		return $this->message;
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroyMessage(): void
	{
		$this->message = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasMessage(): bool
	{
		return $this->message !== null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFrame(Protocols\IFrame $frame): void
	{
		$this->frame = $frame;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFrame(): Protocols\IFrame
	{
		return $this->frame;
	}

	/**
	 * {@inheritdoc}
	 */
	public function destroyFrame(): void
	{
		$this->frame = null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasFrame(): bool
	{
		return $this->frame !== null;
	}

}
