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

### Lunching server

The server side of WebSocket installation is now complete. You should be able to run this from the root of your nette project.

This extension come with Kdyby\Console support, so to start server just type:

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
