<?php
/**
 * Console.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:WebSocket!
 * @subpackage     Logger
 * @since          1.0.0
 *
 * @date           26.02.17
 */

declare(strict_types = 1);

namespace IPub\WebSockets\Logger;

use Nette;

use Psr\Log;

use IPub;

/**
 * WebSockets server output printer
 *
 * @package        iPublikuj:WebSocket!
 * @subpackage     Logger
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class Console implements Log\LoggerInterface
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Formatter\IFormatter
	 */
	private $formatter;

	/**
	 * @param Formatter\IFormatter $formatter
	 *
	 * @return void
	 */
	public function setFormatter(Formatter\IFormatter $formatter)
	{
		$this->formatter = $formatter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function emergency($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->caution($message);

		} else {
			echo 'CAUTION! ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function alert($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->error($message);

		} else {
			echo 'ERROR! ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function critical($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->error($message);

		} else {
			echo 'ERROR! ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function error($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->error($message);

		} else {
			echo 'ERROR! ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function warning($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->warning($message);

		} else {
			echo 'WARNING! ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function notice($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->note($message);

		} else {
			echo 'NOTICE! ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function info($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->note($message);

		} else {
			echo 'INFO: ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function debug($message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->note($message);

		} else {
			echo 'DEBUG: ';

			$this->writeln($message);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function log($level, $message, array $context = [])
	{
		if ($this->formatter) {
			$this->formatter->note($message);

		} else {
			echo 'LOG: ';

			$this->writeln($message);
		}
	}

	/**
	 * @param string $message
	 */
	private function writeln(string $message)
	{
		if ($this->formatter) {
			$this->formatter->writeln($message);

		} else {
			echo $message . "\r\n";
		}
	}
}
