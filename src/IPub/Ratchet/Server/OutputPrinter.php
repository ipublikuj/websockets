<?php
/**
 * OutputPrinter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 * @since          1.0.0
 *
 * @date           26.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Server;

use Nette;

use IPub;
use IPub\Ratchet\Server;

/**
 * Ratchet server output printer
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class OutputPrinter
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Server\Output\IFormatter
	 */
	private $formatter;

	/**
	 * @param Output\IFormatter $formatter
	 *
	 * @return void
	 */
	public function setFormatter(Server\Output\IFormatter $formatter)
	{
		$this->formatter = $formatter;
	}

	/**
	 * @param string $message
	 */
	public function write(string $message)
	{
		if ($this->formatter) {
			$this->formatter->write($message);

		} else {
			echo $message;
		}
	}

	/**
	 * @param string $message
	 */
	public function writeln(string $message)
	{
		if ($this->formatter) {
			$this->formatter->writeln($message);

		} else {
			echo $message ."\r\n";
		}
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function success(string $message)
	{
		if ($this->formatter) {
			$this->formatter->success($message);

		} else {
			echo 'SUCCESS: ';

			$this->writeln($message);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function error(string $message)
	{
		if ($this->formatter) {
			$this->formatter->error($message);

		} else {
			echo 'ERROR! ';

			$this->writeln($message);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function warning(string $message)
	{
		if ($this->formatter) {
			$this->formatter->warning($message);

		} else {
			echo 'WARNING! ';

			$this->writeln($message);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function note(string $message)
	{
		if ($this->formatter) {
			$this->formatter->note($message);

		} else {
			echo 'NOTE: ';

			$this->writeln($message);
		}
	}

	/**
	 * @param string $message
	 *
	 * @return void
	 */
	function caution(string $message)
	{
		if ($this->formatter) {
			$this->formatter->caution($message);

		} else {
			echo 'CAUTION! ';

			$this->writeln($message);
		}
	}
}
