# Ratchet

[![Build Status](https://img.shields.io/travis/iPublikuj/ratchet.svg?style=flat-square)](https://travis-ci.org/iPublikuj/ratchet)
[![Scrutinizer Code Coverage](https://img.shields.io/scrutinizer/coverage/g/iPublikuj/ratchet.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/ratchet/?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/iPublikuj/ratchet.svg?style=flat-square)](https://scrutinizer-ci.com/g/iPublikuj/ratchet/?branch=master)
[![Latest Stable Version](https://img.shields.io/packagist/v/ipub/ratchet.svg?style=flat-square)](https://packagist.org/packages/ipub/ratchet)
[![Composer Downloads](https://img.shields.io/packagist/dt/ipub/ratchet.svg?style=flat-square)](https://packagist.org/packages/ipub/ratchet)
[![License](https://img.shields.io/packagist/l/ipub/ratchet.svg?style=flat-square)](https://packagist.org/packages/ipub/ratchet)

Extension for implementing [Ratchet](http://socketo.me/) WebSockets into [Nette Framework](http://nette.org/)

## Installation

The best way to install ipub/ratchet is using  [Composer](http://getcomposer.org/):

```sh
$ composer require ipub/ratchet
```

After that you have to register extension in config.neon.

```neon
extensions:
	ratchet: IPub\Ratchet\DI\RatchetExtension
```

## Documentation

Learn how to create WebSocket server & controllers in [documentation](https://github.com/iPublikuj/ratchet/blob/master/docs/en/index.md).

***
Homepage [http://www.ipublikuj.eu](http://www.ipublikuj.eu) and repository [http://github.com/iPublikuj/ratchet](http://github.com/iPublikuj/ratchet).
