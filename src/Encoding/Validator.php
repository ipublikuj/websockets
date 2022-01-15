<?php declare(strict_types = 1);

namespace IPub\WebSockets\Encoding;

/**
 * Encoding validation
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Encoding
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Validator implements IValidator
{

	public const UTF8_ACCEPT = 0;
	public const UTF8_REJECT = 1;

	/**
	 * Incremental UTF-8 validator with constant memory consumption (minimal state).
	 *
	 * Implements the algorithm "Flexible and Economical UTF-8 Decoder" by
	 * Bjoern Hoehrmann (http://bjoern.hoehrmann.de/utf-8/decoder/dfa/).
	 */
	protected static $dfa = [
		0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0, // 00..1f
		0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0, // 20..3f
		0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0, // 40..5f
		0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0,
    0, // 60..7f
		1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9,
    9, // 80..9f
		7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7,
    7, // a0..bf
		8,
    8,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2,
    2, // c0..df
		0xa,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x3,
    0x4,
    0x3,
    0x3, // e0..ef
		0xb,
    0x6,
    0x6,
    0x6,
    0x5,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8,
    0x8, // f0..ff
		0x0,
    0x1,
    0x2,
    0x3,
    0x5,
    0x8,
    0x7,
    0x1,
    0x1,
    0x1,
    0x4,
    0x6,
    0x1,
    0x1,
    0x1,
    0x1, // s0..s0
		1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    0,
    1,
    1,
    1,
    1,
    1,
    0,
    1,
    0,
    1,
    1,
    1,
    1,
    1,
    1, // s1..s2
		1,
    2,
    1,
    1,
    1,
    1,
    1,
    2,
    1,
    2,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    2,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1, // s3..s4
		1,
    2,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    2,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    3,
    1,
    3,
    1,
    1,
    1,
    1,
    1,
    1, // s5..s6
		1,
    3,
    1,
    1,
    1,
    1,
    1,
    3,
    1,
    3,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    3,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1, // s7..s8
	];

	/**
	 * Lookup if mbstring is available
	 *
	 * @var bool
	 */
	private $hasMbString = false;

	/**
	 * Lookup if iconv is available
	 *
	 * @var bool
	 */
	private $hasIconv = false;

	public function __construct()
	{
		$this->hasMbString = extension_loaded('mbstring');
		$this->hasIconv = extension_loaded('iconv');
	}

	/**
	 * {@inheritdoc}
	 */
	public function checkEncoding(string $str, string $against): bool
	{
		if ($against === 'UTF-8') {
			return $this->isUtf8($str);
		}

		if ($this->hasMbString) {
			return mb_check_encoding($str, $against);

		} elseif ($this->hasIconv) {
			return $str === iconv($against, sprintf('%s//IGNORE', $against), $str);
		}

		return true;
	}

	/**
	 * @param string $str
	 *
	 * @return bool
	 */
	private function isUtf8(string $str): bool
	{
		if ($this->hasMbString) {
			if (mb_check_encoding($str, 'UTF-8') === false) {
				return false;
			}
		} elseif ($this->hasIconv) {
			if ($str !== iconv('UTF-8', 'UTF-8//IGNORE', $str)) {
				return false;
			}
		}

		$state = self::UTF8_ACCEPT;

		for ($i = 0, $len = strlen($str); $i < $len; $i++) {
			$state = self::$dfa[256 + ($state << 4) + self::$dfa[ord($str[$i])]];

			if ($state === self::UTF8_REJECT) {
				return false;
			}
		}

		return true;
	}

}
