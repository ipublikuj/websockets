<?php declare(strict_types = 1);

namespace IPub\WebSockets\Protocols\RFC6455;

use Countable;
use IPub\WebSockets\Protocols;
use Nette;
use SplDoublyLinkedList;
use UnderflowException;

/**
 * Communication message
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Message implements Protocols\IMessage, Countable
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/** @var SplDoublyLinkedList */
	private $frames;

	public function __construct()
	{
		$this->frames = new SplDoublyLinkedList();
	}

	/**
	 * {@inheritdoc}
	 */
	public function count(): int
	{
		return count($this->frames);
	}

	/**
	 * {@inheritdoc}
	 */
	public function isCoalesced(): bool
	{
		if (count($this->frames) === 0) {
			return false;
		}

		$last = $this->frames->top();

		return $last->isCoalesced() && $last->isFinal();
	}

	/**
	 * @todo Also, I should perhaps check the type...control frames (ping/pong/close) are not to be considered part of a message
	 *
	 * {@inheritdoc}
	 */
	public function addFrame(Protocols\IFrame $fragment): void
	{
		$this->frames->push($fragment);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOpCode(): int
	{
		if (count($this->frames) === 0) {
			throw new UnderflowException('No frames have been added to this message');
		}

		return $this->frames->bottom()->getOpCode();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPayloadLength(): int
	{
		$len = 0;

		foreach ($this->frames as $frame) {
			try {
				$len += $frame->getPayloadLength();

			} catch (UnderflowException $e) {
				// Not an error, want the current amount buffered
			}
		}

		return $len;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPayload(): string
	{
		if (!$this->isCoalesced()) {
			throw new UnderflowException('Message has not been put back together yet');
		}

		$buffer = '';

		foreach ($this->frames as $frame) {
			$buffer .= $frame->getPayload();
		}

		return $buffer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getContents(): string
	{
		if (!$this->isCoalesced()) {
			throw new UnderflowException('Message has not been put back together yet');
		}

		$buffer = '';

		foreach ($this->frames as $frame) {
			$buffer .= $frame->getContents();
		}

		return $buffer;
	}

}
