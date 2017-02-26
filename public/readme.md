# Javascript API Documentation

API for Ratchet is accessible in global object `window.IPub.Ratchet`.

## Include JavaScript files

You have co copy this two files to your document root:

```javascript
/vendor/IPub/ratchet/public/js/autobahn.min.js
/vendor/IPub/ratchet/public/js/ipub.ratchet.js
```

or you could use **bower** components installer od other ways how to include statis files into your webpage.

## Start using client Ratchet API

Once the javascript is included, you can start using IPub.Ratchet to interact with the web socket server.

A *IPub.Ratchet* object is made available in the global scope of the page. This can be used to connect to the server as follows:

```javascript
var webSocket = IPub.Ratchet.initialize('wamp', 'ws://127.0.0.1:8080');
```

of for **message** socket server like this:

```javascript
var webSocket = IPub.Ratchet.initialize('message', 'ws://127.0.0.1:8080');
```

The following commands are available to a IPub.Ratchet object returned by IPub.Ratchet.initialize.

### WAMP

#### IPub.Ratchet.WAMP.on(event, callback)

This allows you to listen for events called by the server. The only events fired currently are **"socket/connect"** and **"socket/disconnect"**.

```javascript
var webSocket = IPub.Ratchet.initialize('wamp', 'ws://127.0.0.1:8080');

webSocket.on('socket/connect', function(session){
    // Session is an Autobahn JS WAMP session.

    console.log('Successfully Connected!');
});

webSocket.on('socket/disconnect', function(error){
    // Error provides you with some insight into the disconnection: error.reason and error.code

    console.log('Disconnected for ' + error.reason + ' with code ' + error.code);
})
```

Clients subscribe to "Topics". Clients publish to those same topics. When this occurs, anyone subscribed will be notified if server broadcast some message.

For a more in depth description of PubSub architecture go and check [Autobahn JS PubSub Documentation](http://autobahn.ws/js/reference_wampv1.html)

* `session.subscribe(topic, function(uri, payload))`
* `session.unsubscribe(topic)`
* `session.publish(topic, event, exclude, eligible)`

These methods are all fairly straightforward, here's an example on using them:

```javascript
webSocket.on('socket/connect', function(session){

    // The callback function in "subscribe" is called everytime an event is published in that channel.
    session.subscribe("acme/channel", function(uri, payload){
        console.log("Received message", payload.msg);
    });

    session.publish('your/channel', 'This is a message!');
})
```

### Message

#### IPub.Ratchet.Message.on(event, callback)

This allows you to listen for events called by the server. The only events fired currently are **"socket/open"**, **"socket/close"**, **"socket/error"** and **"socket/message"**.

```javascript
var webSocket = IPub.Ratchet.initialize('message', 'ws://127.0.0.1:8080');

webSocket.on('socket/open', function(){
    // This event is fired when connection is established

    // Sending data with additional path (more in chapter about routing)
    webSocket.send('/custom/path', 'Message content');

    // or

    // Sending only data
    webSocket.send('Message content');
});

webSocket.on('socket/message', function(msg){
    // New message was recieved from server
    // This event is fired only for simple text responses
});

webSocket.on('socket/error', function(error){
    // Server send error message
});

webSocket.on('socket/close', function(closed){
    // Server ended session

    console.log('Disconnected for ' + closed.reason + ' with code ' + closed.code);
});
```
