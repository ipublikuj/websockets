<?php
/**
 * Test: IPub\WebSockets\Extension
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
 * @date           24.02.17
 */

declare(strict_types = 1);

namespace IPubTests\WebSockets;

use Nette;

use React\EventLoop\LoopInterface;

use Tester;
use Tester\Assert;

use IPub;
use IPub\WebSockets;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require __DIR__ . DS . 'libraries' . DS . 'Application.php';

/**
 * WebSockets extension container test case
 *
 * @package        iPublikuj:WebSockets!
 * @subpackage     Tests
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ExtensionTest extends Tester\TestCase
{
	public function testCompilersServices()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('webSockets.controllers.factory') instanceof WebSockets\Application\Controller\IControllerFactory);

		Assert::true($dic->getService('webSockets.clients.driver.memory') instanceof WebSockets\Clients\Drivers\InMemory);
		Assert::true($dic->getService('webSockets.clients.storage') instanceof WebSockets\Clients\Storage);

		Assert::true($dic->getService('webSockets.routing.router') instanceof WebSockets\Router\IRouter);
		Assert::true($dic->getService('webSockets.routing.generator') instanceof WebSockets\Router\LinkGenerator);

		Assert::true($dic->getService('webSockets.server.wrapper') instanceof WebSockets\Server\Wrapper);
		Assert::true($dic->getService('webSockets.server.loop') instanceof LoopInterface);
		Assert::true($dic->getService('webSockets.server.server') instanceof WebSockets\Server\Server);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
