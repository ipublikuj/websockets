<?php
/**
 * Frame.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 * @since          1.0.0
 *
 * @date           03.03.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Protocols\RFC6455;

use Nette;

use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Protocols;

/**
 * Communication frame
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Frame implements Protocols\IFrame
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 *
	 */
	const OP_CONTINUE = 0;
	const OP_TEXT = 1;
	const OP_BINARY = 2;
	const OP_CLOSE = 8;
	const OP_PING = 9;
	const OP_PONG = 10;

	/**
	 * Closing codes
	 */
	const CLOSE_NORMAL = 1000;
	const CLOSE_GOING_AWAY = 1001;
	const CLOSE_PROTOCOL = 1002;
	const CLOSE_BAD_DATA = 1003;
	const CLOSE_NO_STATUS = 1005;
	const CLOSE_ABNORMAL = 1006;
	const CLOSE_BAD_PAYLOAD = 1007;
	const CLOSE_POLICY = 1008;
	const CLOSE_TOO_BIG = 1009;
	const CLOSE_MAND_EXT = 1010;
	const CLOSE_SRV_ERR = 1011;
	const CLOSE_TLS = 1015;

	const MASK_LENGTH = 4;

	/**
	 * The contents of the frame
	 *
	 * @var string
	 */
	private $data = '';

	/**
	 * Number of bytes received from the frame
	 *
	 * @var int
	 */
	private $bytesReceived = 0;

	/**
	 * Number of bytes in the payload (as per framing protocol)
	 *
	 * @var int
	 */
	private $defPayLen = -1;

	/**
	 * If the frame is coalesced this is true
	 * This is to prevent doing math every time ::isCoalesced is called
	 *
	 * @var bool
	 */
	private $isCoalesced = FALSE;

	/**
	 * The unpacked first byte of the frame
	 *
	 * @var int
	 */
	private $firstByte = -1;

	/**
	 * The unpacked second byte of the frame
	 *
	 * @var int
	 */
	private $secondByte = -1;

	/**
	 * @param string|NULL $payload
	 * @param bool $final
	 * @param int $opCode
	 */
	public function __construct(?string $payload = NULL, bool $final = TRUE, int $opCode = self::OP_TEXT)
	{
		if ($payload === NULL) {
			return;
		}

		$this->defPayLen = strlen($payload);
		$this->firstByte = ($final ? 128 : 0) + $opCode;
		$this->secondByte = $this->defPayLen;
		$this->isCoalesced = TRUE;

		$ext = '';

		if ($this->defPayLen > 65535) {
			$ext = pack('NN', 0, $this->defPayLen);
			$this->secondByte = 127;

		} elseif ($this->defPayLen > 125) {
			$ext = pack('n', $this->defPayLen);
			$this->secondByte = 126;
		}

		$this->data = chr($this->firstByte) . chr($this->secondByte) . $ext . $payload;
		$this->bytesReceived = 2 + strlen($ext) + $this->defPayLen;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isCoalesced() : bool
	{
		if ($this->isCoalesced === TRUE) {
			return TRUE;
		}

		try {
			$payload_length = $this->getPayloadLength();
			$payload_start = $this->getPayloadStartingByte();

		} catch (\UnderflowException $e) {
			return FALSE;
		}

		$this->isCoalesced = $this->bytesReceived >= $payload_length + $payload_start;

		return $this->isCoalesced;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addBuffer(string $buffer) : void
	{
		$len = strlen($buffer);

		$this->data .= $buffer;
		$this->bytesReceived += $len;

		if ($this->firstByte === -1 && $this->bytesReceived !== 0) {
			$this->firstByte = ord($this->data[0]);
		}

		if ($this->secondByte === -1 && $this->bytesReceived >= 2) {
			$this->secondByte = ord($this->data[1]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function isFinal() : bool
	{
		if ($this->firstByte === -1) {
			throw new \UnderflowException('Not enough bytes received to determine if this is the final frame in message');
		}

		return ($this->firstByte & 128) === 128;
	}

	/**
	 * @return bool
	 *
	 * @throws \UnderflowException
	 */
	public function getRsv1() : bool
	{
		if ($this->firstByte === -1) {
			throw new \UnderflowException('Not enough bytes received to determine reserved bit');
		}

		return ($this->firstByte & 64) === 64;
	}

	/**
	 * @return bool
	 *
	 * @throws \UnderflowException
	 */
	public function getRsv2() : bool
	{
		if ($this->firstByte === -1) {
			throw new \UnderflowException('Not enough bytes received to determine reserved bit');
		}

		return ($this->firstByte & 32) === 32;
	}

	/**
	 * @return bool
	 *
	 * @throws \UnderflowException
	 */
	public function getRsv3() : bool
	{
		if ($this->firstByte === -1) {
			throw new \UnderflowException('Not enough bytes received to determine reserved bit');
		}

		return ($this->firstByte & 16) === 16;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isMasked() : bool
	{
		if ($this->secondByte === -1) {
			throw new \UnderflowException("Not enough bytes received ({$this->bytesReceived}) to determine if mask is set");
		}

		return ($this->secondByte & 128) === 128;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMaskingKey() : string
	{
		if (!$this->isMasked()) {
			return '';
		}

		$start = 1 + $this->getNumPayloadBytes();

		if ($this->bytesReceived < $start + self::MASK_LENGTH) {
			throw new \UnderflowException('Not enough data buffered to calculate the masking key');
		}

		return substr($this->data, $start, self::MASK_LENGTH);
	}

	/**
	 * Create a 4 byte masking key
	 *
	 * @return string
	 */
	public function generateMaskingKey() : string
	{
		$mask = '';

		for ($i = 1; $i <= self::MASK_LENGTH; $i++) {
			$mask .= chr(rand(32, 126));
		}

		return $mask;
	}

	/**
	 * Apply a mask to the payload
	 *
	 * @param string $maskingKey if NULL is passed a masking key will be generated
	 *
	 * @return void
	 *
	 * @throws \OutOfBoundsException
	 * @throws Exceptions\InvalidArgumentException If there is an issue with the given masking key
	 */
	public function maskPayload(?string $maskingKey = NULL)
	{
		if ($maskingKey === NULL) {
			$maskingKey = $this->generateMaskingKey();
		}

		if (strlen($maskingKey) !== self::MASK_LENGTH) {
			throw new Exceptions\InvalidArgumentException(sprintf('Masking key must be %s characters', self::MASK_LENGTH));
		}

		if (extension_loaded('mbstring') && TRUE !== mb_check_encoding($maskingKey, 'US-ASCII')) {
			throw new \OutOfBoundsException('Masking key MUST be ASCII');
		}

		$this->unMaskPayload();

		$this->secondByte = $this->secondByte | 128;
		$this->data[1] = chr($this->secondByte);

		$this->data = substr_replace($this->data, $maskingKey, $this->getNumPayloadBytes() + 1, 0);

		$this->bytesReceived += self::MASK_LENGTH;
		$this->data = substr_replace($this->data, $this->applyMask($maskingKey), $this->getPayloadStartingByte(), $this->getPayloadLength());
	}

	/**
	 * Remove a mask from the payload
	 *
	 * @return void
	 *
	 * @throws \UnderFlowException if the frame is not coalesced
	 */
	public function unMaskPayload() : void
	{
		if (!$this->isCoalesced()) {
			throw new \UnderflowException('Frame must be coalesced before applying mask');
		}

		if ($this->isMasked()) {
			$maskingKey = $this->getMaskingKey();

			$this->secondByte = $this->secondByte & ~128;
			$this->data[1] = chr($this->secondByte);

			$this->data = substr_replace($this->data, '', $this->getNumPayloadBytes() + 1, self::MASK_LENGTH);

			$this->bytesReceived -= self::MASK_LENGTH;
			$this->data = substr_replace($this->data, $this->applyMask($maskingKey), $this->getPayloadStartingByte(), $this->getPayloadLength());
		}
	}

	/**
	 * Apply a mask to a string or the payload of the instance
	 *
	 * @param string $maskingKey   The 4 character masking key to be applied
	 * @param string|NULL $payload A string to mask or null to use the payload
	 *
	 * @return string
	 *
	 * @throws \UnderflowException If using the payload but enough hasn't been buffered
	 */
	public function applyMask(string $maskingKey, ?string $payload = NULL) : string
	{
		if ($payload === NULL) {
			if (!$this->isCoalesced()) {
				throw new \UnderflowException('Frame must be coalesced to apply a mask');
			}

			$payload = substr($this->data, $this->getPayloadStartingByte(), $this->getPayloadLength());
		}

		$applied = '';

		for ($i = 0, $len = strlen($payload); $i < $len; $i++) {
			$applied .= $payload[$i] ^ $maskingKey[$i % self::MASK_LENGTH];
		}

		return $applied;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOpCode() : int
	{
		if (-1 === $this->firstByte) {
			throw new \UnderflowException('Not enough bytes received to determine opCode');
		}

		return ($this->firstByte & ~240);
	}

	/**
	 * Gets the decimal value of bits 9 (10th) through 15 inclusive
	 *
	 * @return int
	 *
	 * @throws \UnderflowException If the buffer doesn't have enough data to determine this
	 */
	private function getFirstPayloadVal() : int
	{
		if (-1 === $this->secondByte) {
			throw new \UnderflowException('Not enough bytes received');
		}

		return $this->secondByte & 127;
	}

	/**
	 * @return int (7|23|71) Number of bits defined for the payload length in the fame
	 *
	 * @throws \UnderflowException
	 */
	private function getNumPayloadBits() : int
	{
		if ($this->secondByte === -1) {
			throw new \UnderflowException('Not enough bytes received');
		}

		// By default 7 bits are used to describe the payload length
		// These are bits 9 (10th) through 15 inclusive
		$bits = 7;

		// Get the value of those bits
		$check = $this->getFirstPayloadVal();

		// If the value is 126 the 7 bits plus the next 16 are used to describe the payload length
		if ($check >= 126) {
			$bits += 16;
		}

		// If the value of the initial payload length are is 127 an additional 48 bits are used to describe length
		// Note: The documentation specifies the length is to be 63 bits, but I think that's a typo and is 64 (16+48)
		if ($check === 127) {
			$bits += 48;
		}

		return $bits;
	}

	/**
	 * This just returns the number of bytes used in the frame to describe the payload length (as opposed to # of bits)
	 *
	 * @see getNumPayloadBits
	 */
	private function getNumPayloadBytes() : int
	{
		return (1 + $this->getNumPayloadBits()) / 8;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPayloadLength() : int
	{
		if ($this->defPayLen !== -1) {
			return $this->defPayLen;
		}

		$this->defPayLen = $this->getFirstPayloadVal();

		if ($this->defPayLen <= 125) {
			return $this->getPayloadLength();
		}

		$byte_length = $this->getNumPayloadBytes();

		if ($this->bytesReceived < 1 + $byte_length) {
			$this->defPayLen = -1;
			throw new \UnderflowException('Not enough data buffered to determine payload length');
		}

		$len = 0;

		for ($i = 2; $i <= $byte_length; $i++) {
			$len <<= 8;
			$len += ord($this->data[$i]);
		}

		$this->defPayLen = $len;

		return $this->getPayloadLength();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPayloadStartingByte() : int
	{
		return 1 + $this->getNumPayloadBytes() + ($this->isMasked() ? self::MASK_LENGTH : 0);
	}

	/**
	 * @todo Consider not checking mask, always returning the payload, masked or not
	 *
	 * {@inheritdoc}
	 */
	public function getPayload() : string
	{
		if (!$this->isCoalesced()) {
			throw new \UnderflowException('Can not return partial message');
		}

		$payload = substr($this->data, $this->getPayloadStartingByte(), $this->getPayloadLength());

		if ($this->isMasked()) {
			$payload = $this->applyMask($this->getMaskingKey(), $payload);
		}

		return $payload;
	}

	/**
	 * @todo This is untested, make sure the substr is right - trying to return the frame w/o the overflow
	 *
	 * Get the raw contents of the frame
	 */
	public function getContents() : string
	{
		return substr($this->data, 0, $this->getPayloadStartingByte() + $this->getPayloadLength());
	}

	/**
	 * @todo Consider returning new Frame
	 *
	 * Sometimes clients will concatenate more than one frame over the wire
	 * This method will take the extra bytes off the end and return them
	 *
	 * @return string
	 */
	public function extractOverflow() : string
	{
		if ($this->isCoalesced()) {
			$endPoint = $this->getPayloadLength();
			$endPoint += $this->getPayloadStartingByte();

			if ($this->bytesReceived > $endPoint) {
				$overflow = substr($this->data, $endPoint);
				$this->data = substr($this->data, 0, $endPoint);

				return $overflow;
			}
		}

		return '';
	}
}
