<?php
/**
 * ServerCommand.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Commands
 * @since          1.0.0
 *
 * @date           26.02.17
 */

declare(strict_types = 1);

namespace IPub\Ratchet\Commands;

use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Style;
use Symfony\Component\Console\Output;

use IPub;
use IPub\Ratchet\Server;

/**
 * Ratchet server command
 *
 * @package        iPublikuj:Ratchet!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ServerCommand extends Console\Command\Command
{
	/**
	 * @var Server\Server
	 */
	private $server;

	/**
	 * @var Server\OutputPrinter
	 */
	private $printer;

	/**
	 * @param Server\Server $server
	 * @param Server\OutputPrinter $printer
	 * @param string|NULL $name
	 */
	public function __construct(
		Server\Server $server,
		Server\OutputPrinter $printer,
		string $name = NULL
	) {
		parent::__construct($name);

		$this->server = $server;
		$this->printer = $printer;
	}

	/**
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('ipub:ratchet:start')
			->setDescription('Start WebSocket server.');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output)
	{
		$io = new Style\SymfonyStyle($input, $output);

		$io->text([
			'',
			'+------------------+',
			'| WebSocket server |',
			'+------------------+',
			'',
		]);

		$formatter = new Server\Output\SymfonyOutput($io);

		$this->printer->setFormatter($formatter);

		$this->server->run();
	}
}
