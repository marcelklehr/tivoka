# Tivoka\Client\Request

## Tivoka\Client\Request->id
## Tivoka\Client\Request->method
## Tivoka\Client\Request->params

## Tivoka\Client\Request->request
 * {String}

JSON-RPC request as a JSON-encoded string

## Tivoka\Client\Request->response
 * {String}

The JSON-RPC response as received from the server

## Tivoka\Client\Request->result
The result of the remote procedure call.

## Tivoka\Client\Request->error
The error code, of the error occurred on invoking the remote procedure call.

## Tivoka\Client\Request->errorMessage
The error message, of the error occurred on invoking the remote procedure call.

## Tivoka\Client\Request->errorData
Additional data, associtaed with the error occurred on invoking the remote procedure call.

## Tivoka\Client\Request->responseHeaders
 * {Array}

The HTTP response headers as an array of header=>value.

## Tivoka\Client\Request->responseHeadersRaw
 * {Array}

The HTTP response headers as an array of raw header lines.

## Tivoka\Client\Request->__construct( $method, [$params] )
 * `$method` {String} The remote procedure to invoke
 * `$params` {Array} Additional params for the remote procedure

Constructs a new JSON-RPC request object

## Tivoka\Client\Request->sendTo( $target )
 * `$target` {String} URL

Send this request to a remote server directly.

## Tivoka\Client\Request->isError()
 * Return: {Bool}

Determines whether an error occured.

## Tivoka\Client\Request->getRequest( $spec )
 * (used internally)

Get the JSON-RPC request as a JSON-encoded string

## Tivoka\Client\Request->setHeaders( $raw_headers )
 * (used internally)

Save and parse the HTTP headers.

## Tivoka\Client\Request->setResponse( $response )
 * (used internally)

Interprets the response.

## Tivoka\Client\Request->interpretResponse( $json_struct )
 * (used internally)

Interprets the parsed response.

## Tivoka\Client\Request::http_parse_headers( $headers )
 * protected

Parses headers as returned by magic variable $http_response_header

## Tivoka\Client\Request::interpretResult( $spec, $assoc, $id)
 * protected

Checks whether the given response is a valid result

## Tivoka\Client\Request::interpretError( $spec, $assoc, $id)
 * protected

Checks whether the given response is valid and an error

## Tivoka\Client\Request::prepareRequest( $spec, $id, $method, [$params])
 * protected

Builds the request structure, depending on the spec version.
 
## Tivoka\Client\Request::uuid( )
 * (used internally)
 * Return: {String} A v4 uuid.