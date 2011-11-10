# Tivoka #
A leightweight JSON-RPC client and server implementation for PHP.
(c) 2011, Marcel Klehr


## The Goal ##
**JSON-RPC** is the alternative of complicated and unintuitive data type handling in XML-RPC.
JSON-RPC uses the JavaScript Object Notation (JSON) and therefore natively supports string, integer, float and array data types!

The goal of this project is to provide an easy-to-use, specification compatible and object-oriented JSON-RPC implementation for PHP.  
This version is based on the [JSON-RPC 2.0 specification proposal](https://groups.google.com/group/json-rpc/web/json-rpc-2-0).
Support for the official JSON-RPC protocol is planned in near future.

## Using Tivoka ##
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

## It's free ##
**Tivoka is free software**; you can redistribute it and/or modify it under the 
terms of the **GNU General Public License** as published by the Free Software Foundation;
either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program;
if not, see <http://www.gnu.org/licenses/>.

## See also ##
* Submit any bug or suggestion to the [Issue Tracker](http://github.com/marcelklehr/tivoka/issues)
* Download the latest version [here](https://github.com/marcelklehr/tivoka/tags)