# Example application bootstrap file

If you are not able to use [Kdyby\Console](https://github.com/Kdyby/Console) or you want to use server loop your way, here is a example how to create your bootstrap file:

```php
use Nette\Configurator;
use IPub\WebSockets;

define('DS', DIRECTORY_SEPARATOR);
define('BASE_DIR', __DIR__ . DS . '..');
define('WWW_DIR', BASE_DIR . DS . 'web');
define('APP_DIR', BASE_DIR . DS . 'app');
define('CONFIG_DIR', APP_DIR . DS . 'config');
define('TEMP_DIR', BASE_DIR . DS . 'tmp');
define('VENDOR_DIR', BASE_DIR . DS . 'vendor');

// Load all libraries via composer
require VENDOR_DIR . DS . 'autoload.php';

$params = [];
// Absolute filesystem path to this web root
$params['wwwDir'] = realpath(__DIR__ . '/../web');
// Absolute filesystem path to the application root
$params['appDir'] = __DIR__;

// Create app configurator
$configurator = new Configurator();
$configurator->setTempDirectory(TEMP_DIR);

// Load app configuration
$configurator->addConfig(CONFIG_DIR . DS . 'config.neon');
// Define variables
$configurator->addParameters([
	'baseDir'        => BASE_DIR,
	'vendorDir'      => VENDOR_DIR,
	'appDir'         => APP_DIR,
	'tempDir'        => TEMP_DIR,
	'wwwDir'         => realpath(WWW_DIR),
	'rootDir'        => realpath(BASE_DIR),
	'locale'         => 'en_US',
]);

// Create app container
$container = $configurator->createContainer();

// Run server
$container->getByType(WebSockets\Server\Server::class)->run();
```

NOTE: This is only example, you have to change it according your server/application configuration.
