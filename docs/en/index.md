# WebSockets

This is a [Nette](http://nette.org/) extension designed to bring you an easy implementation of a websockets server.

## What can you do with this extension

Make real time application like

* Chat Application
* Real time notifications
* Browser games

## Installation

### Install extension package

The best way how to install ipub/websockets is using the [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/websockets
```

### Register into the Nette application

After that you have to register the extension in the config.neon.

```neon
extensions:
    webSockets: IPub\WebSockets\DI\WebSocketsExtension
```

### Configure extension

This extension has a lot of configuration options:

```php
    # WebSockets server
    webSockets:
        clients:
            storage:
                driver: @clients.driver.memory  // Here you can pass service name of your clients storage driver implementation
                ttl:    0                       // If your driver support TTL, here you can define it
        // Main server configuration
        server:
            httpHost:   localhost
            port:       8080        // The websocket server will listen on this port
            address:    0.0.0.0
        routes: []      // Routes definition
        mapping: []     // Controllers mapping
```

### Define routes

This extension is using routes same as you know them from Nette and Controllers that are similar to Presenters. So you have to define some routes in neon configuration:

```php
    # WebSockets server
    webSockets:
        routes:
            '/[!<locale [a-z]{2,4}>/]some-path/<myParameter>/<otherParameter>' : 'ControllerName:'
            '/[!<locale [a-z]{2,4}>/]other-path/<myParameter>/<otherParameter>' : 'SecondControllerName:'
```

As you can see, the definition is in nette compatible way.

Or if you don't want to define routes in neon, you can define socket routes in a service

```php
services:
    - {class: App\RouterFactory, tags: [ipub.websockets.routes]}
```

and the factory could look like this:

```php
<?php
namespace App;

use IPub\WebSockets\Router\Route;
use IPub\WebSockets\Router\RouteList;

class RouterFactory
{
    public static function createRouter() : RouteList
    {
        $router = new RouteList;
        $router[] = new Route('/[!<locale [a-z]{2,4}>/]some-path/<myParameter>/<otherParameter>', 'ControllerName:');
        $router[] = new Route('/[!<locale [a-z]{2,4}>/]other-path/<myParameter>/<otherParameter>', 'SecondControllerName:');

        return $router;
    }
}
```

The tag in the service is **important**, extension will search for services with this tag and attach routes to the extension router.

In special cases, when you don't want to create Controllers, you could use built-in micro Controller which is similar to the Nette micro Presenter. 

```php
<?php
namespace App;

use IPub\WebSockets\Router\Route;
use IPub\WebSockets\Router\RouteList;

class RouterFactory
{
    public static function createRouter() : RouteList
    {
        $router = new RouteList;
        $router[] = new Route('/[!<locale [a-z]{2,4}>/]some-path/<myParameter>/<otherParameter>', function(){
            // Place your code here
        });

        return $router;
    }
}
```

When you pass the callback as a route metadata, the micro Controller will be used.

### Create a WebSocket application

As the next step you have to define which application have to be used with the server.
You could use [ipub/websockets-message](https://github.com/iPublikuj/websockets-message) for classic socket messaging or [ipub/websockets-wamp](https://github.com/iPublikuj/websockets-wamp) or you could define your own application. 

### Lunching the server

The server side of WebSocket installation is now complete. You should be able to run this from the root of your nette project.

Server have to be started like other Nette apps. All what you have to do is create container like in yout normal nette bootstrap, get server service and start it.

```php
$configurator = new \Nette\Configurator;

$container = $configurator->createContainer();

$container->getByType(\IPub\WebSockets\Server\Server::class)->run();
```

More info in [example](https://github.com/ipublikuj/websockets/blob/master/docs/en/ExampleBootstrap.md)

This extension come also with Symfony console support.
All console commands are automatically registered as services. So with symfony command you could start server like this. 

```sh
php web/index.php ipub:websockets:start
```

If everything is successful, you will see something similar to the following:

```sh
 +------------------+
 | WebSocket server |
 +------------------+


 ! [NOTE] Starting IPub\WebSockets

 ! [NOTE] Launching WebSockets WS Server on: localhost:8888
```

This means the websocket server is now up and running ! 

**From here, only the websocket server is running ! That doesn't mean you can send a message, to subscribe, to publish or even to call. Follow next steps in separated packages ([ipub/websockets-message](https://github.com/iPublikuj/websockets-message) or [ipub/websockets-wamp](https://github.com/iPublikuj/websockets-wamp)) do it :)**

## More

- [Read more how attach action on server events](https://github.com/iPublikuj/websockets/blob/master/docs/en/events.md)
- [Read more how create secured connection to server](https://github.com/iPublikuj/websockets/blob/master/docs/en/ssl.md)
