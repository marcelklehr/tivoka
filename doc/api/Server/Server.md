# Tivoka\Server\Server

## Tivoka\Server\Server->host
 * {Object} The object whose methods to replicate

## Tivoka\Server\Server->input
 * {String} The parsed json input as an associative array

## Tivoka\Server\Server->reponse
 * {Array} A list of response structs, ready for json_encode

## Tivoka\Server\Server->spec
 * {Integer} Spec constant

## Tivoka\Server\Server->hide_errors
* {Bool}

## Tivoka\Server\Server->__construct( $host )
 * `$host`{Object} An object whose methods will be provided for invokation or
 * `$host`{Array} An array mapping method names to callbacks
 
Constructss a Server object.

## Tivoka\Server\Server->useSpec( $spec )
 * `$spec` {String} The spec version (e.g.: "2.0")
 * Return: {Tivoka\Server\Server} self

Sets the spec version to use for this server.

## Tivoka\Server\Server->hideErrors()
 * Return: {Tivoka\Server\Sever} self

If invoked, the server will try to hide all PHP errors, to prevent them from obfuscating the output.

## Tivoka\Server\Server->dispatch()
Starts processing of the HTTP input. This will stop further execution of the script (so, this should be the last line of your script).

## Tivoka\Server\Server->process( $request )
 * (used internally)

Processes the passed request.

## Tivoka\Server\Server->returnResult( $id, $result )
 * (used internally)

Receives the computed result for a request.

## Tivoka\Server\Server->returnError( $id, $code, [$message, [$data]] )
 * (used internally)

Receives an error, occurred while computing a request.

## Tivoka\Server\Server->respond()
 * (used internally)

Outputs the processed response and stops further execution of php.

## Tivoka\Server\Server::interpretRequest($spec, $assoc)
 * (used internally)

Validates and sanitizes a normal request.

## Tivoka\Server\Server::interpretNotification($spec, $assoc)
 * (used internally)

Validates and sanitizes a notification.

## Tivoka\Server\Server::interpretBatch($assoc)
 * (used internally)

Validates a batch request.