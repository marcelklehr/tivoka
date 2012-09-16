## Sending a request

You want to send a JSON-RPC request, right?  
So, the first question we need to answer to is: Where are we going to send it to?

For demonstration purposes, let's assume we want to send a JSON-RPC request to `http://example.com/api/json-rpc` (although, of course, this resource doesn't provide much useful data...).

```php
<?php
$target = 'http://example.com/api/json-rpc';
?>
```

Now, we simply connect to this URL, using the following method:

```php
<?php
$connection = Tivoka\Client::connect($server);
?>
```

To set a specific spec version, call `->useSpec()`:
```php
<?php
$connection->useSpec('1.0');
?>
```
The default spec version is `'2.0'`.

Now, we have a connection, we also need a request to send. Let's assume, the server on the other end of the connection implements a method called `distance`, which calculates the distance between two cities.

```php
<?php
$request = Tivoka\Client::request('distance', array('London', 'Sydney'));
// or
$request = new Tivoka\Client\Request('distance', array('London', 'Sydney'));
?>
```

Now, we simply send the request:

```php
<?php
$connection->send($request);
?>
```

We will find the result of our request in `$request->result`. And if there was an error, we will find the error code in `$request->error`, the corresponding error message in `$request->errorMessage` and additional error data in `$request->errorData`. We can find out about errors by calling `$request->isError()`.

```php
<?php
if(!$request->isError()) print $request->result;
else {
  print 'Error '.$request->error.': '.$request->errorMessage;
  var_dump($request->errorData);
}
?>
```

This code snippet will display, an error message with additional error data, in case of a server error...
```
Error -32601: Method not found. Provided methods are: 
array(3) {
  [0]=>
  string(1) "distance"
  [1]=>
  string(1) "location"
  [2]=>
  string(1) "route"
}
```

...and the result, in case the request was successful.

```
16983.04km
```

## Sending a notification

This time, we only want to let the server know, where we are. We don't need a response, so we simply use a notification.

Again, we have to set up the connection at first:
```php
<?php
$connection = Tivoka\Client::connect($target);
?>
```

Then we create the notification.
```php
<?php
$request = Tovka\Client::notification('location', array('19L 463742 8249133'));
// or
$request = new Tovka\Client\Notification('location', array('19L 463742 8249133'));
?>
```

Again, we send our notification using `$connection->send()`, but we don't have to wait for a response, because, as defined in the spec, the server won't send one.

```php
<?php
$connection->send($request);
?>
```

Now the server knows, that we are no longer in London, but swimming in Lake Titicaca in South America (These are UTM coordiantes).

## Short cuts
The above can be simplified using the short cut method `sendRequest()`, which implicitly creates a request object for us.
```php
<?php
$request = Tivoka\Client::connect($target)->sendRequest('distance', array('London', 'Sydney'));
print $request->result; // This prints: 16983.04km
?>
```

This also works for notifications:
```php
<?php
Tivoka\Client::connect($target)->sendNotification('location', array('19L 463742 8249133'));
?>
```

## Sending batch requests
If you have multiple requests to send, you could of course `send()` them all one by one...

```php
<?php
$connection->send($request1);
$connection->send($request2);
$connection->send($request3);
?>
```

...but, you can much easier send them all at once, as a batch request.

```php
<?php
$connection->send($request1, $request2, $request3);
?>
```

In some cases you will find, that you have an array of requests, passed by some other part of your code, that you want to send. Here you go:
```php
<?php
$array = array($request1, $request2, $request3);
$connection->send($array);
?>
```

## Native remote interface
An even simpler solution to invoke remote methods is the native remote interface.

This lets you call remote methods, as if they were implemented directly in PHP.
```php
<?php
$client = Tivoka\Client::connect($server)->getNativeInterface();

$client->distance('London', 'Sydney'); // returns '16983.04km'
?>
```

However, this method raises an exception for every server error, so be sure to add some try-catch statement.