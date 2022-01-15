# WebSockets

[![Build Status](https://badgen.net/github/checks/ipublikuj/websockets/master?cache=300&style=flast-square)](https://github.com/ipublikuj/websockets)
[![Licence](https://badgen.net/packagist/license/ipub/websockets?cache=300&style=flast-square)](https://github.com/ipublikuj/websockets/blob/master/LICENSE.md)
[![Code coverage](https://badgen.net/coveralls/c/github/ipublikuj/websockets?cache=300&style=flast-square)](https://coveralls.io/github/ipublikuj/websockets)

![PHP](https://badgen.net/packagist/php/ipub/websockets?cache=300&style=flast-square)
[![Downloads total](https://badgen.net/packagist/dt/ipub/websockets?cache=300&style=flast-square)](https://packagist.org/packages/ipub/websockets)
[![Latest stable](https://badgen.net/packagist/v/ipub/websockets/latest?cache=300&style=flast-square)](https://packagist.org/packages/ipub/websockets)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

An extension for implementing WebSockets into the [Nette Framework](http://nette.org/)

## Installation

The best way how to install ipub/websockets is using [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/websockets
```

After that you have to register the extension in the config.neon.

```neon
extensions:
	webSockets: IPub\WebSockets\DI\WebSocketsExtension
```

## Documentation

Learn how to create a web socket server & controllers in [documentation](https://github.com/iPublikuj/websockets/blob/master/docs/en/index.md).

***
Homepage [https://www.ipublikuj.eu](https://www.ipublikuj.eu) and repository [http://github.com/iPublikuj/websockets](http://github.com/iPublikuj/websockets).
