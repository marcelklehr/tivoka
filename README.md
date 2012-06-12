# JSON-RPC done right #
client and server for PHP 5.3

Do JSON-RPC. With Tivoka. It's as easy as that!  
For convenience, you can easily switch between [JSON-RPC 1.0](http://json-rpc.org/wiki/specification) and [JSON-RPC 2.0](http://jsonrpc.org/specification) without having to change your code.

 - Download [latest version](https://github.com/marcelklehr/tivoka/zipball/master) or install it through PEAR (see below)
 - Have a look at the [documentation](https://github.com/marcelklehr/tivoka/wiki)
 - Submit any bugs, suggestions or questions to the [issue tracker](http://github.com/marcelklehr/tivoka/issues)

Learn more about JSON-RPC at <http://jsonrpc.org/>.

## Examples ##
These are just some quick examples. For more details see the [website](http://marcelklehr.github.com/tivoka/).

Do a request
```php
<?php
$connection = Tivoka\Client::connect('http://exapmle.com/api')
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
Tivoka\Server::provide($methods)->dispacht();
?>
```

## Installing through PEAR

Run the following in your console:
```sh
$ pear channel-discover pearhub.org
$ pear install pearhub/tivoka
```

Now you can include tivoka using `include 'tivoka/include.php'`

## License ##
**MIT License** -- (See the `LICENSE` file)

## Changelog ##

2.0.3

 * Added HTTPS support
 * target scheme is now treated case insensitive

***

2.0.2

 * Introduced new directory structure
 * Fixed #10
 * Some Exception messages changed slightly to be more specific

***

2.0.1

 * Patched http method spelling (make uppercase, so all servers accept it)

***

2.0.0

 * complete Code base rework
 * major API change
 * removed Response Class
 * Added aa number of shortcuts
 * Implemented native remote interface

***