<?php declare(strict_types = 1);

namespace Tests\Cases;

use Doctrine\ORM;
use IPub\WebSockets;
use Nette;
use Nette\DI;
use Nettrine;
use Ninjify\Nunjuck\TestCase\BaseMockeryTestCase;

abstract class BaseTestCase extends BaseMockeryTestCase
{

	/** @var string[] */
	protected array $additionalConfigs = [];

	/** @var DI\Container */
	private DI\Container $container;

	/** @var ORM\EntityManagerInterface|null */
	private ?ORM\EntityManagerInterface $em = null;

	/**
	 * {@inheritDoc}
	 */
	protected function setUp(): void
	{
		parent::setUp();

		$this->container = $this->createContainer($this->additionalConfigs);
	}

	/**
	 * @return DI\Container
	 */
	protected function getContainer(): DI\Container
	{
		return $this->container;
	}

	/**
	 * @return ORM\EntityManagerInterface
	 */
	protected function getEntityManager(): ORM\EntityManagerInterface
	{
		if ($this->em === null) {
			/** @var ORM\EntityManagerInterface $em */
			$em = $this->getContainer()->getByType(Nettrine\ORM\EntityManagerDecorator::class);

			$this->em = $em;
		}

		return $this->em;
	}

	/**
	 * @return void
	 */
	protected function generateDbSchema(): void
	{
		$schema = new ORM\Tools\SchemaTool($this->getEntityManager());
		$schema->createSchema($this->getEntityManager()->getMetadataFactory()
			->getAllMetadata());
	}

	/**
	 * @param string[] $additionalConfigs
	 *
	 * @return Nette\DI\Container
	 */
	protected function createContainer(array $additionalConfigs = []): Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . '/../../common.neon');

		foreach ($additionalConfigs as $additionalConfig) {
			if ($additionalConfig && file_exists($additionalConfig)) {
				$config->addConfig($additionalConfig);
			}
		}

		WebSockets\DI\WebSocketsExtension::register($config);

		return $config->createContainer();
	}

	/**
	 * @param string $serviceType
	 * @param object $serviceMock
	 *
	 * @return void
	 */
	protected function mockContainerService(
		string $serviceType,
		object $serviceMock
	): void {
		$foundServiceNames = $this->getContainer()->findByType($serviceType);

		foreach ($foundServiceNames as $serviceName) {
			$this->replaceContainerService($serviceName, $serviceMock);
		}
	}

	/**
	 * @param string $serviceName
	 * @param object $service
	 *
	 * @return void
	 */
	private function replaceContainerService(string $serviceName, object $service): void
	{
		$this->getContainer()->removeService($serviceName);
		$this->getContainer()->addService($serviceName, $service);
	}

}
