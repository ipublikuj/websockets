# WebSockets

[![Build Status](https://img.shields.io/travis/iPublikuj/websockets.svg?style=flat-square)](https://travis-ci.org/iPublikuj/websockets)
[![Scrutinizer Code Coverage](https://img.shields.io/scrutinizer/coverage/g/iPublikuj/websockets.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/websockets/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/iPublikuj/websockets.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/websockets/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/ipub/websockets.svg?style=flat-square)](https://packagist.org/packages/ipub/websockets)
[![Composer Downloads](https://img.shields.io/packagist/dt/ipub/websockets.svg?style=flat-square)](https://packagist.org/packages/ipub/websockets)
[![License](https://img.shields.io/packagist/l/ipub/websockets.svg?style=flat-square)](https://packagist.org/packages/ipub/websockets)

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
Homepage [http://www.ipublikuj.eu](http://www.ipublikuj.eu) and repository [http://github.com/iPublikuj/websockets](http://github.com/iPublikuj/websockets).
