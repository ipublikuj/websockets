<?php declare(strict_types = 1);

namespace IPub\WebSockets\Server;

use IPub\WebSockets\Entities;
use Throwable;

interface IWrapper
{

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	public function handleOpen(Entities\Clients\IClient $client): void;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param string $message
	 *
	 * @return void
	 */
	public function handleMessage(Entities\Clients\IClient $client, string $message): void;

	/**
	 * @param Entities\Clients\IClient $client
	 *
	 * @return void
	 */
	public function handleClose(Entities\Clients\IClient $client): void;

	/**
	 * @param Entities\Clients\IClient $client
	 * @param Throwable $ex
	 *
	 * @return void
	 */
	public function handleError(Entities\Clients\IClient $client, Throwable $ex): void;

}
