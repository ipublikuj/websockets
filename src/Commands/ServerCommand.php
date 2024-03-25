<?php declare(strict_types = 1);

namespace IPub\WebSockets\Commands;

use IPub\WebSockets\Exceptions;
use IPub\WebSockets\Logger;
use IPub\WebSockets\Server;
use Psr\Log;
use Symfony\Component\Console;
use Symfony\Component\Console\Input;
use Symfony\Component\Console\Output;
use Symfony\Component\Console\Style;

/**
 * WebSockets server command
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Commands
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ServerCommand extends Console\Command\Command
{
    protected static $defaultName = 'ipub:websockets:start';
	
	/** @var Server\Server */
	private $server;

	/** @var Log\LoggerInterface|Log\NullLogger|null */
	private $logger;

	/**
	 * @param Server\Server $server
	 * @param Log\LoggerInterface|null $logger
	 * @param string|null $name
	 */
	public function __construct(
		Server\Server $server,
		?Log\LoggerInterface $logger = null,
		?string $name = null
	) {
		parent::__construct($name);

		$this->server = $server;
		$this->logger = $logger ?? new Log\NullLogger();
	}

	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this
			->setName(self::$defaultName)
			->setDescription('Start WebSocket server.');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(Input\InputInterface $input, Output\OutputInterface $output): void
	{
		$io = new Style\SymfonyStyle($input, $output);

		$io->text([
			'',
			'+------------------+',
			'| WebSocket server |',
			'+------------------+',
			'',
		]);

		if ($this->logger instanceof Logger\Console) {
			$this->logger->setFormatter(new Logger\Formatter\Symfony($io));
		}

		try {
			$this->server->create();
			$this->server->run();

		} catch (Exceptions\TerminateException $ex) {
			$this->server->stop();
		}
	}

}
