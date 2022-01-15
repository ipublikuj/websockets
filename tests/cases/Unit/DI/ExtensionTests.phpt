<?php declare(strict_types = 1);

namespace Tests\Cases;

use IPub\WebSockets\Application;
use IPub\WebSockets\Clients;
use IPub\WebSockets\Router;
use IPub\WebSockets\Server;
use React\EventLoop\LoopInterface;
use Tester\Assert;

require_once __DIR__ . '/../../../bootstrap.php';
require_once __DIR__ . '/../BaseTestCase.php';

/**
 * @testCase
 */
final class ExtensionTests extends BaseTestCase
{

	public function testFunctional(): void
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('webSockets.controllers.factory') instanceof Application\Controller\IControllerFactory);

		Assert::true($dic->getService('webSockets.clients.driver.memory') instanceof Clients\Drivers\InMemory);
		Assert::true($dic->getService('webSockets.clients.storage') instanceof Clients\Storage);

		Assert::true($dic->getService('webSockets.routing.router') instanceof Router\IRouter);
		Assert::true($dic->getService('webSockets.routing.generator') instanceof Router\LinkGenerator);

		Assert::true($dic->getService('webSockets.server.wrapper') instanceof Server\Wrapper);
		Assert::true($dic->getService('webSockets.server.loop') instanceof LoopInterface);
		Assert::true($dic->getService('webSockets.server.server') instanceof Server\Server);
	}

}

$test_case = new ExtensionTests();
$test_case->run();
