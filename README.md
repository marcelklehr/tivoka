# JSON-RPC done right #
a leightweight JSON-RPC client and server implementation for PHP 5

The goal of this project is to provide an easy-to-use, specification compatible and object-oriented JSON-RPC implementation for PHP.  
Tivoka allows you to choose between [JSON-RPC 1.0](http://json-rpc.org/wiki/specification) and [JSON-RPC 2.0](http://jsonrpc.org/specification) specs.

 - Have a look at a few [examples](http://marcelklehr.github.com/tivoka/)
 - Download [latest version](https://github.com/marcelklehr/tivoka/zipball/2.0.0)
 - Submit any bugs, suggestions or questions to the [Issue Tracker](http://github.com/marcelklehr/tivoka/issues)
 - Learn more about JSON-RPC at <http://jsonrpc.org/>

## Examples ##
These are just some quick examples. For more details see the [website](http://marcelklehr.github.com/tivoka/).

Using the native remote interface

```php
<?php
Tivoka::createClient('http://exapmle.com/api')->substract(51, 9);// 42
?>
```

Creating a server

```php
<?php
Tivoka::createServer(array(
	'substract' => function($req) {
		$result = $req->params[0] - $request->params[1];
		return $req->result($result);
	}
));
?>
```

## License ##
**GNU General Public License** - as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.  
See the `LICENSE` file.