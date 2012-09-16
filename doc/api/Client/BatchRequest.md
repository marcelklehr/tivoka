# Tivoka\Client\BatchRequest
 * Extends: {Tivoka\Client\Request}

A batch request

## Tivoka\Client\BatchRequest->responseHeadersRaw
 * {Array} An array of headers (strings) as in `$http_response_header`

## Tivoka\Client\BatchRequest->responseHeaders
 * {Array} An array of response headers parsed as header=>value

## Tivoka\Client\BatchRequest->__construct( $batch )
 * `$batch` {Array} A list of requests to include, each must be a Tivoka\Client\Request

Constructs a new JSON-RPC batch request. All values of type other than Tivoka\Client\Request will be silently ignored.

## Tivoka\Client\BatchRequest->getRequest( $spec )
 * `$spec` {Integer} A Tivoka spec constant
 * Throws: {Tivoka\Exception\SpecException}
 * Return: {String} the JSON-encoded request source.
 
## Tivoka\Client\BatchRequest->interpretResponse( $json_struct )
 * `$json_struct` {Array} Parsed JSON response, represented as an array
 * Throws: {Tivoka\Exception\SyntaxException}

Interprets the parsed response

## Tivoka\Client\BatchRequest->setHeaders( $raw_headers )
 * `$raw_headers` {Array} An array of strings coming from $http_response_header magic var

Save and parse the HTTP response headers