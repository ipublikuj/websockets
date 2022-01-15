<?php
/**
 * websockets-server.php
 *
 * @copyright      More in LICENSE.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:WebSockets!
 * @subpackage     bin
 * @since          2.0.0
 *
 * @date           19.07.16
 */

declare(strict_types = 1);

use IPub\WebSockets;

$rootDir = getcwd();
$libsDir = $rootDir . DIRECTORY_SEPARATOR . 'vendor';
$wwwDir = $rootDir . DIRECTORY_SEPARATOR . 'www';
$appDir = $rootDir . DIRECTORY_SEPARATOR . 'app';

foreach (['log', 'temp'] as $dir) {
	if (!is_dir($rootDir . DIRECTORY_SEPARATOR . $dir)) {
		mkdir($rootDir . DIRECTORY_SEPARATOR . $dir);
	}
}

if (!file_exists($libsDir . DIRECTORY_SEPARATOR . 'autoload.php')) {
	die('autoload.php file can not be found.');
}

require_once $libsDir . DIRECTORY_SEPARATOR . 'autoload.php';

$configurator = new Nette\Configurator;
$configurator->addParameters([
	'appDir' => $appDir,
	'wwwDir' => $wwwDir,
]);

//$configurator->setDebugMode(TRUE);  // Debug mode HAVE TO BE enabled on production server
$configurator->enableDebugger($packagesDir . DIRECTORY_SEPARATOR . 'log');

$configurator->setTempDirectory($packagesDir . DIRECTORY_SEPARATOR . 'temp');
$configurator->addConfig(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.neon');

$container = $configurator->createContainer();

$container->getByType(WebSockets\Server\Server::class)->run();
