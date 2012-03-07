# JSON-RPC done right #
a leightweight JSON-RPC client and server implementation for PHP 5

The goal of this project is to provide an easy-to-use, specification compatible and object-oriented JSON-RPC implementation for PHP.  
This version is based on the [JSON-RPC 2.0 specification proposal](https://groups.google.com/group/json-rpc/web/json-rpc-2-0).
Support for the official JSON-RPC protocol version is planned in near future.

Download [latest version](https://github.com/marcelklehr/tivoka/tags)  
Learn more about JSON-RPC at <http://jsonrpc.org/>
Submit any bugs or suggestions to the [Issue Tracker](http://github.com/marcelklehr/tivoka/issues)

## Project status ##
I hardly had time to work on this project during the last year, but got a brain wave just now and will try pushing it to 2.0.0.
The thing that troubled me the most was that the current API is not very usable (<ou will agree, i think, especially when looking at the examples below).  
I am trying to solve this with a new API, which is already committed but still leaves some work to be done. Stay tuned!

## Using Tivoka ##
*This is an example of the 'old' v2.0.0b API.*
Here's an example for you to quickly get the clue (can be found in the `/example` directory):

### Client ###
```php
/*
 * STEP 1
 * Load the client files
 */
include('../client.inc.php');

/*
 * STEP 2
 * Build a request
 */
$request = new Tivoka_Request('1', 'demo.substract', array(43,1));

/*
 * STEP 3
 * Connect to a server and send the request
 */
Tivoka_Client::connect('http://example.com/my/jsonrpc/server.php')->send($request);


/*
 * Display the result...
 */
print $request->response->result;
```

### Server

```php
/**
 * STEP 1
 * Load the server files
 */
include('../server.inc.php');

/**
 * STEP 2
 * Define an array of remote methods
 */
$methods = array(
	'demo.substract' => function($request)
	{
		$request->returnResult($request->params[0] - $request->params[1]);
		return TRUE;
	}
);

/**
 * STEP 3
 * Implement the methods
 */
$methodhost = new Tivoka_ArrayObject($methods);

/**
 * STEP 4
 * Init the server and process the request
 */
Tivoka_Server::start($methodhost);
```
Of course you can also use your own Object instead of using Tivoka_ArrayObject, but it's very useful for testing purposes.

## License ##
**GNU General Public License** as published by the Free Software Foundation;
either version 3 of the License, or (at your option) any later version.  
See the LICENSE file.