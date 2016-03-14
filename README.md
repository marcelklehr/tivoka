# Tivoka
[JSON-RPC](http://jsonrpc.org/) client and server for PHP 5.3+

* Easily switch between the [v1.0](http://json-rpc.org/wiki/specification) and [v2.0](http://jsonrpc.org/specification) specs
* HTTP, TCP and Websocket transports available

## Examples ##
These are just some quick examples. Check out the docs in [`/doc/`](https://github.com/marcelklehr/tivoka/tree/develop/doc).

Do a request through HTTP...
```php
<?php
$connection = Tivoka\Client::connect('http://example.com/api')
$request = $connection->sendRequest('substract', array(51, 9));
print $request->result;// 42
?>
```

...or plain TCP
```php
<?php
$connection = Tivoka\Client::connect(array('host' => 'example.com', 'port' => 1234))
$request = $connection->sendRequest('substract', array(51, 9));
print $request->result;// 42
?>
```

...or WebSocket
```php
<?php
$connection = Tivoka\Client::connect('ws://example.com/api')
$request = $connection->sendRequest('substract', array(51, 9));
print $request->result;// 42
?>
```

Create a server
```php
<?php
$methods = array(
    'substract' => function($params) {
        list($num1, $num2) = $params
        return $num1 - $num2;
    }
);
Tivoka\Server::provide($methods)->dispatch();
?>
```

## Links
 - Have a look at the documentation in `doc/`
 - Submit any bugs, suggestions or questions to the [issue tracker](http://github.com/marcelklehr/tivoka/issues)

## Installation

### Install composer package
1. Set up `composer.json` in your project directory:
```
{
  "require":{"tivoka/tivoka":"*"}
}
```

2. Run [composer](http://getcomposer.org/doc/00-intro.md#installation):
```sh
$ php composer.phar install
```

Now, `include 'vendor/autoload.php'`

## License ##
Copyright 2011-2012 by Marcel Klehr
MIT License.

## Changelog ##
3.5.1

 * Fix Http Connection

3.5.0

 * Add support for cookies if curl is installed (thanks to @oxan)

3.4.2

 * Fix HTTP via curl: Don't add a trailing newline for http headers (thanks to @oskarcafe)

3.4.1

 * Http: Use cURL if available (thanks to @hschletz)

3.4.0

 * Adding options to set/override request headers in WebSocket (thanks to @fiddur)

3.3.0

 * Add websocket transport (thanks to @fiddur)

3.2.1

 * Fix #41: Fix Exception catcher in Tivoka\Server\Server::process (thanks to @ikulis)

3.2.0

 * Feature: Plain TCP connections (revamped a lot of our internals along the way! thanks go out to @rafalwrzeszcz)
 * Feature: Configurable connection timeout

3.1.0

 * Fix #27: json-rpc response[result] may be `null` (thanks to @vaab)
 * Feature: Allow setting of request headers and expose response headers (thanks to @vaab)
 * Fix bug with client-side notifications
 * Add docs in `doc/`

3.0.1

 * Fix a typo, that used to screw up things when throwing an exception (thanks to @gahr)


3.0.0

 * use Namespaces (no longer supports php5.2)
 * new factory classes (per server/client)
 * Requests no longer require $id argument
 * Dramatically simplified serverside usage
 * Fluid spec version setter
 * Now available as composer package


2.0.3

 * Added HTTPS support
 * target scheme is now treated case insensitive


2.0.2

 * Introduced new directory structure
 * Fixed #10
 * Some Exception messages changed slightly to be more specific


2.0.1

 * Patched http method spelling (make uppercase, so all servers accept it)


2.0.0

 * complete Code base rework
 * major API change
 * removed Response Class
 * Added aa number of shortcuts
 * Implemented native remote interface

