# WebSockets

This is a [Nette](http://nette.org/) extension designed to bring you easy implementation of websockets server.

## What can you do with this extension

Make real time application like

* Chat Application
* Real time notification
* Browser games

More commonly, all application who meet real time.

## Installation

### Install extension package

The best way to install ipub/websockets is using [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/websockets
```

### Register into Nette application

After that you have to register extension in config.neon.

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
                driver: @clients.driver.memory  // Here you could pass service name of your clients storage driver implementation
                ttl:    0                       // If your driver support TTL, here you could define it
        // Main server configuration
        server:
            httpHost:   localhost
            port:       8080        // Server port. On this port the socket server will listen on
            address:    0.0.0.0
            type:       message     // Here you define type of the server. Allowed options are `message` or `wamp`
        routes: []      // Routes definition
        mapping: []     // Controllers mapping
```

### Define routes

This extension is using routes like you know from Nette and Controllers which are similar to Presenters. So you have to define some routes in neon configuration:

```php
    # WebSockets server
    webSockets:
        routes:
            '/[!<locale [a-z]{2,4}>/]some-path/<myParameter>/<otherParameter>' : 'ControllerName:'
            '/[!<locale [a-z]{2,4}>/]other-path/<myParameter>/<otherParameter>' : 'SecondControllerName:'
```

as you can see, definition is in nette compatible way.

Or if you don't want to define routes in neon, you could use define routes as service

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

The tag in service is **important**, extension will search for services with this tag and attach routes to extension router.

In special cases, when you don't want to create Controllers, you could use built-in micro Controller which is similar to Nette micro Presenter. 

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

When you pass callback as route metadata, micro Controller will be used.

### Create webSocket application

As next step, you have to define which application have to be used with server.
You could use [ipub/websockets-message](https://github.com/iPublikuj/websockets-message) for classic socket messaging or [ipub/websockets-wamp](https://github.com/iPublikuj/websockets-wamp) or you could define your own application. 

### Lunching server

The server side of WebSocket installation is now complete. You should be able to run this from the root of your nette project.

This extension come with [Kdyby/Console](https://github.com/kdyby/console) support, so to start server just type:

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

**From here, only the websocket server is running ! That doesn't mean you can send message, subscribe, publish or even call. Follow next steps to do it :)**
