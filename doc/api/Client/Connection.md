# Tivoka\Client\Connection

## Tivoka\Client\Connection->target
 * {String}

## Tivoka\Client\Connection->headers
 * {Array}

## Tivoka\Client\Connection->spec
 * {Integer} Tivoka spec constant

## Tivoka\Client\Connection->__construct( $target )
 * `$target` {String} URL
 * Throws: {Tivoka\Exception\Exception}

## Tivoka\Client\Connection->useSpec( $spec )
 * `$spec` {String} The spec version as a string (e.g.: "2.0")
 * Return: {Tivoka\Client\Connection} self

Sets the spec version to use for this connection.

## Tivoka\Client\Connection->setHeader( $header, $value )
 * `$header` {String}
 * `$value` {String}
 * Return: {Tivoka\Client\Connection} self

Sets the HTTP request headers to use for upcoming JSON-RPC requests

## Tivoka\Client\Connection->send( $request )
## Tivoka\Client\Connection->send( $request... )
 * `$request` {Tivoka\Client\Request} If more than one request is passed in the arguments, they will be bundled in a batch request.
 * Throws: {Tivoka\Exception\Exception}
 * Throws: {Tivoka\Exception\ConnectionException}
 * Return: The request that was sent (the implicitly created batch request, in case of more than one parameters)
 
Sends a JSON-RPC request.

## Tivoka\Client\Connection->sendRequest( $method, [$params] )
 * `$method` {String}
 * `$params` {Array}
 * Return: {Tivoka\Client\Request}
 
Send a request directly

## Tivoka\Client\Connection->sendNotification( $method, [$params] )
 * `$method` {String}
 * `$params` {Array}
 * Return: {Tivoka\Client\Request}
 
Send a notification directly.

## Tivoka\Client\Connection->getNativeInterface()
 * Return: {Tivoka\Client\NativeInterface}
 
Creates a native remote interface for the target server
