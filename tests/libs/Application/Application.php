<?php declare(strict_types = 1);

namespace Tests\Libs\Application;

use IPub\WebSockets\Application\IApplication;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;
use Throwable;

class Application implements IApplication
{

	/**
	 * {@inheritdoc}
	 */
	public function handleOpen(Entities\Clients\IClient $client, Http\IRequest $httpRequest): void
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleClose(Entities\Clients\IClient $client, Http\IRequest $httpRequest): void
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleError(Entities\Clients\IClient $client, Http\IRequest $httpRequest, Throwable $ex): void
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function handleMessage(Entities\Clients\IClient $from, Http\IRequest $httpRequest, string $message): void
	{
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubProtocols(): array
	{
		return [];
	}

}
