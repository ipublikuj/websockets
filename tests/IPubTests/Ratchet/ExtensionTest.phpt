<?php
/**
 * Test: IPub\Ratchet\Extension
 * @testCase
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Ratchet!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           24.02.17
 */

declare(strict_types = 1);

namespace IPubTests\Ratchet;

use Nette;

use Ratchet\Session\Serialize\HandlerInterface;
use React\EventLoop\LoopInterface;

use Tester;
use Tester\Assert;

use IPub;
use IPub\Ratchet;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

class ExtensionTest extends Tester\TestCase
{
	public function testCompilersServices()
	{
		$dic = $this->createContainer();

		Assert::true($dic->getService('ratchet.controllers.factory') instanceof Ratchet\Application\IControllerFactory);

		Assert::true($dic->getService('ratchet.users.repository') instanceof Ratchet\Users\Repository);

		Assert::true($dic->getService('ratchet.clients.driver.memory') instanceof Ratchet\Clients\Drivers\InMemory);
		Assert::true($dic->getService('ratchet.clients.storage') instanceof Ratchet\Clients\Storage);

		Assert::true($dic->getService('ratchet.router') instanceof Ratchet\Router\IRouter);

		Assert::true($dic->getService('ratchet.application.message') instanceof Ratchet\Message\Provider);

		Assert::true($dic->getService('ratchet.server.wrapper') instanceof Ratchet\Server\Wrapper);
		Assert::true($dic->getService('ratchet.server.loop') instanceof LoopInterface);
		Assert::true($dic->getService('ratchet.server.server') instanceof Ratchet\Server\Server);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		Ratchet\DI\RatchetExtension::register($config);

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
