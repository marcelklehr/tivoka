# JSON-RPC done right #
client and server for PHP 5.3+

Do JSON-RPC. With Tivoka. It's as easy as that!  
For convenience, you can easily switch between [JSON-RPC 1.0](http://json-rpc.org/wiki/specification) and [JSON-RPC 2.0](http://jsonrpc.org/specification) without having to change your code.

Learn more about JSON-RPC at <http://jsonrpc.org/>.

## Examples ##
These are just some quick examples. For more details see the [wiki](https://github.com/marcelklehr/tivoka/wiki).

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
Tivoka\Server::provide($methods)->dispatch();
?>
```

## Links
 - Have a look at the [documentation](https://github.com/marcelklehr/tivoka/wiki)
 - Submit any bugs, suggestions or questions to the [issue tracker](http://github.com/marcelklehr/tivoka/issues)

## Installation

### Install composer package
1. Set up `composer.json`:

```
{
  require:{"tivoka/tivoka":"*"}
}
```

2. Run [composer](http://getcomposer.org/doc/00-intro.md#installation):

```sh
$ php composer.phar install
```

Now, `include 'vendor/autoload.php'`

### Install through pear
1. Run the following:

```sh
$ pear channel-discover pearhub.org
$ pear install pearhub/tivoka
```

Now, `include 'tivoka/include.php'`

### Download
1. Grab the source using `git clone https://github.com/marcelklehr/tivoka.git`.  
Or [download](http://github.com/marcelklehr/tivoka/zipball/master) it.

Now, `include 'path/to/tivoka/include.php'`

## License ##
Copyright 2011-2012 by Marcel Klehr  
MIT License.

## Changelog ##
3.0.1

 * Fix a typo, that used to screw up things when throwing an exception (thanks to gahr)

***

3.0.0

 * use Namespaces (no longer supports php5.2)
 * new factory classes (per server/client)
 * Requests no longer require $id argument
 * Dramatically simplified serverside usage
 * Fluid spec version setter
 * Now available as composer package

***

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