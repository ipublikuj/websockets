<?php
/**
 * SymfonyOutput.php
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

namespace IPub\Ratchet\Server\Output;

use Symfony\Component\Console;

/**
 * Ratchet server symfony console output formater
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Server
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class SymfonyOutput implements IFormatter
{
	/**
	 * @var Console\Style\SymfonyStyle
	 */
	private $output;

	/**
	 * @param Console\Style\SymfonyStyle $output
	 */
	public function __construct(Console\Style\SymfonyStyle $output)
	{
		$this->output = $output;
	}

	/**
	 * {@inheritdoc}
	 */
	public function write(string $message)
	{
		$this->output->write($message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function writeln(string $message)
	{
		$this->output->writeln($message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function success(string $message)
	{
		$this->output->success($message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function error(string $message)
	{
		$this->output->error($message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function warning(string $message)
	{
		$this->output->warning($message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function note(string $message)
	{
		$this->output->note($message);
	}

	/**
	 * {@inheritdoc}
	 */
	public function caution(string $message)
	{
		$this->output->caution($message);
	}
}
