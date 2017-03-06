# Events

Sometimes you will need to perform a server side action when a user connects or disconnects. IPub/WebSocket will fire events for many reasons:

* Server starting
* Client Connects
* Client Disconnects
* Incoming message
* After processing message

Event system in this extension is independent. You could attach events directly in your DI in Nette way or you could use a third-party extension like [Kdyby/Events](https://github.com/kdyby/events)

## Creating event listener

This listener is for Nette way implementation.

```php
namespace App\Events;

class OnClientConnectedHandler
{
    /**
     * App\SomeService
     */
    private $someService;

    public function __construct(App\SomeService $someService)
    {
        $this->someService = $someService
    }

    /**
     * @param IPub\WebSockets\Entities\Clients\IClient $client
     * @param IPub\WebSockets\Http\IRequest $httpRequest
     *
     * @return void
     */
    public function __invoke(IPub\WebSockets\Entities\Clients\IClient $client, IPub\WebSockets\Http\IRequest $httpRequest)
    {
        // Do your stuff here
    }
}

```

Now you have to register this listener as service and attach it to the server wrapper:

```php
$containerBuilder = .....

$containerBuilder->addDefinition('onClientConnected')
    ->setClass(App\Events\OnClientConnectedHandler::class);

$serverWrapper = $containerBuilder->getDefinitionByType(IPub\WebSockets\Server\Wrapper::class);
$serverWrapper->addSetup('$service->onClientConnected[] = ?', ['@' . $this->prefix('events.onClientConnected')]);
```

Now you could add other events.

```php
IPub\WebSockets\Server\Wrapper::onClientConnected(IPub\WebSockets\Entities\Clients\IClient $client, IPub\WebSockets\Http\IRequest $httpRequest)
IPub\WebSockets\Server\Wrapper::onClientDisconnected(IPub\WebSockets\Entities\Clients\IClient $client, IPub\WebSockets\Http\IRequest $httpRequest)
IPub\WebSockets\Server\Wrapper::onIncomingMessage(IPub\WebSockets\Entities\Clients\IClient $client, IPub\WebSockets\Http\IRequest $httpRequest, string $message)
IPub\WebSockets\Server\Wrapper::onAfterIncomingMessage(IPub\WebSockets\Entities\Clients\IClient $client, IPub\WebSockets\Http\IRequest $httpRequest)
IPub\WebSockets\Server\Wrapper::onClientError(IPub\WebSockets\Entities\Clients\IClient $client, IPub\WebSockets\Http\IRequest $httpRequest)
IPub\WebSockets\Server\Server::onStart(React\EventLoop\LoopInterface $eventLoop, IPub\WebSockets\Server\Server $server)
```

Where:
* **Client** is actual connection entity
* **Request** is parsed incoming request
* **Server** is server instance

More details could be found in respective entities.
