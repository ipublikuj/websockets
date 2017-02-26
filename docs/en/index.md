# Ratchet

IPub/Ratchet is a Nette extension designed to bring together WebSocket functionality in a easy to use application architecture.

Much like [Socket.IO]() it provides both server side and client side code ensuring you have to write as little as possible to get your app up and running.

Powered By [Ratchet](http://socketo.me/) and [Autobahn JS](http://autobahn.ws/js/), with [Nette](https://nette.org/)

## What can you do with this extension

Make real time application like

* Chat Application
* Real time notification
* Browser games

More commonly, all application who meet real time.

## Main features

* PHP Websocket server (IO / WAMP & Messages)
* PHP Websocket client (IO / WAMP & Messages)
* JS Websocket client (IO / WAMP & Messages)
* Controller Nette way routing
* PubSub
* Remote procedure call

## Installation

### Install extension package

The best way to install ipub/ratchet is using  [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/ratchet
```

### Register into Nette application

After that you have to register extension in config.neon.

```neon
extensions:
    ratchet: IPub\Ratchet\DI\RatchetExtension
```

### Configure extension

This extension has a lot of configuration options:

```php
    # Ratchet server
    ratchet:
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
        // WAMP server configuration
        wamp:
            version:    v1  // Protocol version
            topics:         // Topics configuration
                storage:
                    driver: @wamp.topics.driver.memory  // Here you could pass service name of your topics storage driver implementation
                    ttl:    0                           // If your driver support TTL, here you could define it
        session: true   // Enable od disable session
        routes: []      // Routes definition
        mapping: []     // Controllers mapping
```

### Lunching server

The server side of WebSocket installation is now complete. You should be able to run this from the root of your nette project.

This extension come with Kdyby\Console support, so to start server just type:

```sh
php web/index.php ipub:ratchet:start
```

If everything is successful, you will see something similar to the following:

```sh
 +------------------+
 | WebSocket server |
 +------------------+


 ! [NOTE] Starting IPub\WebSocket

 ! [NOTE] Launching Ratchet WS Server on: localhost:8888
```

This means the websocket server is now up and running ! 

**From here, only the websocket server is running ! That doesn't mean you can send message, subscribe, publish or even call. Follow next steps to do it :)**

### Next Steps

For further documentations on how to use WebSocket, please continue with the client side setup.

* [Setup Client Javascript](https://github.com/iPublikuj/ratchet/blob/master/public/readme.md)
