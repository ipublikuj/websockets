<?php declare(strict_types = 1);

namespace IPub\WebSockets\Protocols;

use IPub\WebSockets\Application;
use IPub\WebSockets\Encoding;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Http;
use Nette;

/**
 * The latest version of the WebSocket protocol
 *
 * @link           http://tools.ietf.org/html/rfc6455
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Protocols
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @todo           Unicode: return mb_convert_encoding(pack("N",$u), mb_internal_encoding(), 'UCS-4BE');
 */
class RFC6455 implements IProtocol
{

	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * Handshake hash
	 */
	public const GUID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

	/** @var RFC6455\HandshakeVerifier */
	private $verifier;

	/**
	 * A lookup of the valid close codes that can be sent in a frame
	 *
	 * @var array
	 */
	private $closeCodes = [];

	/** @var Encoding\IValidator */
	private $validator;

	public function __construct()
	{
		$this->verifier = new RFC6455\HandshakeVerifier($this->getVersion());

		$this->setCloseCodes();

		$this->validator = new Encoding\Validator();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getVersion(): int
	{
		return 13;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVersion(Http\IRequest $httpRequest): bool
	{
		$version = (int) (string) $httpRequest->getHeader('Sec-WebSocket-Version');

		return $this->getVersion() === $version;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function doHandshake(Http\IRequest $httpRequest): Http\IResponse
	{
		if ($this->verifier->verifyAll($httpRequest) !== true) {
			return new Http\Response(Http\IResponse::S400_BAD_REQUEST);
		}

		return new Http\Response(Http\IResponse::S101_SWITCHING_PROTOCOLS, [
			'Upgrade'              => 'websocket',
			'Connection'           => 'Upgrade',
			'Sec-WebSocket-Accept' => $this->sign((string) $httpRequest->getHeader('Sec-WebSocket-Key')),
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleMessage(Entities\Clients\IClient $client, Application\IApplication $application, string $data = ''): void
	{
		$overflow = '';

		$webSocket = $client->getWebSocket();

		if (!$webSocket->hasMessage()) {
			$webSocket->setMessage($this->newMessage());
		}

		// There is a frame fragment attached to the connection, add to it
		if (!$webSocket->hasFrame()) {
			$webSocket->setFrame($this->newFrame());
		}

		$webSocket->getFrame()->addBuffer($data);

		if ($webSocket->getFrame()->isCoalesced()) {
			/** @var RFC6455\Frame $frame */
			$frame = $webSocket->getFrame();

			if ($frame->getRsv1() !== false ||
				$frame->getRsv2() !== false ||
				$frame->getRsv3() !== false
			) {
				$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

				return;
			}

			if (!$frame->isMasked()) {
				$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

				return;
			}

			$opCode = $frame->getOpCode();

			if ($opCode > 2) {
				if ($frame->getPayloadLength() > 125 || !$frame->isFinal()) {
					$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

					return;
				}

				switch ($opCode) {
					case RFC6455\Frame::OP_CLOSE:
						$closeCode = 0;

						$bin = $frame->getPayload();

						if ($bin === []) {
							$this->close($client);

							return;
						}

						if (strlen($bin) >= 2) {
							[$closeCode] = array_merge(unpack('n*', substr($bin, 0, 2)));
						}

						if (!$this->isValidCloseCode($closeCode)) {
							$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

							return;
						}

						if (!$this->validator->checkEncoding(substr($bin, 2), 'UTF-8')) {
							$this->close($client, RFC6455\Frame::CLOSE_BAD_PAYLOAD);

							return;
						}

						$frame->unMaskPayload();

						$this->send($client, $frame);

						return;

					case RFC6455\Frame::OP_PING:
						$client->send($this->newFrame($frame->getPayload(), true, RFC6455\Frame::OP_PONG));
						break;

					case RFC6455\Frame::OP_PONG:
						break;

					default:
						$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

						return;
				}

				$overflow = $webSocket->getFrame()->extractOverflow();

				$webSocket->destroyFrame();

				unset($frame, $opCode);

				if (strlen($overflow) > 0) {
					$this->handleMessage($client, $application, $overflow);
				}

				return;
			}

			$overflow = $webSocket->getFrame()->extractOverflow();

			/** @var RFC6455\Message $message */
			$message = $webSocket->getMessage();

			if ($frame->getOpCode() === RFC6455\Frame::OP_CONTINUE && count($message) === 0) {
				$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

				return;
			}

			if (count($message) > 0 && $frame->getOpCode() !== RFC6455\Frame::OP_CONTINUE) {
				$this->close($client, RFC6455\Frame::CLOSE_PROTOCOL);

				return;
			}

			$webSocket->getMessage()->addFrame($webSocket->getFrame());
			$webSocket->destroyFrame();
		}

		if ($webSocket->getMessage()->isCoalesced()) {
			$parsed = $webSocket->getMessage()->getPayload();

			$webSocket->destroyMessage();

			if (!$this->validator->checkEncoding($parsed, 'UTF-8')) {
				$this->close($client, RFC6455\Frame::CLOSE_BAD_PAYLOAD);

				return;
			}

			$application->handleMessage($client, $client->getRequest(), $parsed);
		}

		if (strlen($overflow) > 0) {
			$this->handleMessage($client, $application, $overflow);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function send(Entities\Clients\IClient $client, $payload): void
	{
		if (!$client->getWebSocket()->isClosing()) {
			if (!$payload instanceof IData) {
				$payload = new RFC6455\Frame($payload);
			}

			$client->getConnection()->write($payload->getContents());
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function close(Entities\Clients\IClient $client, ?int $code = null): void
	{
		if ($client->getWebSocket()->isClosing()) {
			return;
		}

		$code = $code ?? 1000;

		if ($code instanceof IData) {
			$this->send($client, $code);

		} else {
			$this->send($client, new RFC6455\Frame(pack('n', $code), true, RFC6455\Frame::OP_CLOSE));
		}

		$client->getConnection()->end();

		$client->getWebSocket()->setClosing(true);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->getVersion();
	}

	/**
	 * Used when doing the handshake to encode the key, verifying client/server are speaking the same language
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function sign(string $key): string
	{
		return base64_encode(sha1($key . self::GUID, true));
	}

	/**
	 * @return RFC6455\Message
	 */
	private function newMessage(): RFC6455\Message
	{
		return new RFC6455\Message();
	}

	/**
	 * @param string|null $payload
	 * @param bool|null $final
	 * @param int|null $opCode
	 *
	 * @return RFC6455\Frame
	 */
	private function newFrame(?string $payload = null, bool $final = true, int $opCode = RFC6455\Frame::OP_TEXT): RFC6455\Frame
	{
		return new RFC6455\Frame($payload, $final, $opCode);
	}

	/**
	 * Determine if a close code is valid
	 *
	 * @param int|string $val
	 *
	 * @return bool
	 */
	private function isValidCloseCode($val): bool
	{
		if (array_key_exists($val, $this->closeCodes)) {
			return true;
		}

		return $val >= 3000 && $val <= 4999;
	}

	/**
	 * Creates a private lookup of valid, private close codes
	 */
	private function setCloseCodes(): void
	{
		$this->closeCodes[RFC6455\Frame::CLOSE_NORMAL] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_GOING_AWAY] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_PROTOCOL] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_BAD_DATA] = true;
		//$this->closeCodes[RFC6455\Frame::CLOSE_NO_STATUS] = true;
		//$this->closeCodes[RFC6455\Frame::CLOSE_ABNORMAL] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_BAD_PAYLOAD] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_POLICY] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_TOO_BIG] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_MAND_EXT] = true;
		$this->closeCodes[RFC6455\Frame::CLOSE_SRV_ERR] = true;
		//$this->closeCodes[RFC6455\Frame::CLOSE_TLS] = true;
	}

}
