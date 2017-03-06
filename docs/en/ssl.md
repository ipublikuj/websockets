# SSL Configuration

For wss:// connection you need to have installed certificates on your server. This extension come with full SSL support without tunneling or proxies.

Secured connection could be enabled trough extension configuration:

```php
    # WebSockets server
    webSockets:
        server:
            httpHost:   localhost
            port:       8080        // Server port. On this port the socket server will listen on
            address:    0.0.0.0
            secured:
                enable: true
                sslSettings:
                    local_cert: /path/to/your/certificate
                    local_pk: /path/to/your/private/certificate
```

Importatn part is **sslSettings**. This configuration accept all options which are defined for [TLS/SSL context](http://php.net/manual/en/context.ssl.php).

Once you fill this part, you could connect to your server with secured protocol wss://path.to.server.com
