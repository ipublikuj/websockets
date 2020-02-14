<?php
/**
 * Test: IPub\WebSockets\Libraries
 *
 * @testCase
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           04.03.17
 */

declare(strict_types = 1);

namespace IPubTests\WebSockets\Libraries;

use Throwable;

use IPub\WebSockets;
use IPub\WebSockets\Application\IApplication;
use IPub\WebSockets\Entities;
use IPub\WebSockets\Http;

class Application implements IApplication
{
	/**
	 * {@inheritdoc}
	 */
	function handleOpen(Entities\Clients\IClient $client, Http\IRequest $httpRequest) : void
	{

	}

	/**
	 * {@inheritdoc}
	 */
	function handleClose(Entities\Clients\IClient $client, Http\IRequest $httpRequest) : void
	{

	}

	/**
	 * {@inheritdoc}
	 */
	function handleError(Entities\Clients\IClient $client, Http\IRequest $httpRequest, Throwable $ex) : void
	{

	}

	/**
	 * {@inheritdoc}
	 */
	function handleMessage(Entities\Clients\IClient $from, Http\IRequest $httpRequest, string $message) : void
	{

	}

	/**
	 * {@inheritdoc}
	 */
	function getSubProtocols() : array
	{
		return [];
	}
}
