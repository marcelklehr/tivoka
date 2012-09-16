# Tivoka\Client
The public interface to all client-side tivoka features

## Tivoka\Client::connect( $target )
 * `$target` {String} the URL of the target server
 * Return: {Tivoka\Client\Connection}
Initializes a Connection to a remote server

## Tivoka\Client::createRequest( $method, [$params] )
## Tivoka\Client::request( $method, [$params] )
 * `$method` {String} The method to invoke
 * `$params` {Array} The parameters
 * Return: {Tivoka\Client\Request}

Creates a request

## Tivoka\Client::createNotification( $method, [$params] )
## Tivoka\Client::notification( $method, [$params] )
 * `$method` {String} The method to invoke
 * `$params` {Array} The parameters
 * Return: {Tivoka\Client\Notification}
 
Creates a notification

## Tivoka\Client::createBatch( $request... )
## Tivoka\Client::batch( $request... )
## Tivoka\Client::createBatch( $request )
## Tivoka\Client::batch( $request )
 * `$request` {Tivoka\Client\Request} All arguments will be part of the batch
 * `$request` {Array} All elements of the array will be part of the batch
 * Throws: {Tivoka\Exception\Exception}
 * Return: {Tivoka\Client\BatchRequest}

Creates a batch request