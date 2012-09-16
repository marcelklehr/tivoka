## Setting up some remote methods
We can do this by either declaring a new class...

```php
<?php
class GeoServer {
  function distance($first, $second) {
    // calculate distance
  }

  function setLocation($utm) {
    // set UTM coordinates
  }

  function route($destination) {
    // calculate route from current location to $destination
  }
}

$geo_server = new GeoServer;
?>
```

...or by collecting callbacks to different functions or methods from different classes.  
This is also a good way to rename some of your methods.

```php
<?php
$geo_server = array(
  'distance' => array($geo, 'distance'),
  'location' => array($geo, 'setLocation'),
  'route' => array($geo, 'route'),
);
?>
```

Of course, you can also pass anonymous functions.

## Creating the server
To start the server, just do the following:
```php
<?php
Tivoka\Server::provide($geo_server)->dispatch();
?>
```

If you want to use a specific spec version call `->useSpec($version)`:
```php
<?php
Tivoka\Server::provide($geo_server)->useSpec('1.0')->dispatch();
?>
```

The default spec version is `'2.0'`.

## Retuning a result
The above will not work, as expected. Why?

All remote methods will be passed an array of all arguments as the first argument, since JSON-RPC also allows for named parameters.

```php
<?php
function($params) {
  $dist = calc_distance($params[0], $params[1]);
  return $dist;
}
?>
```
If you want to issue an error from within your remote method, you can throw `Tivoka\Exception\ProcedureException`.
```php
<?php
use Tivoka\Exception\ProcedureException;
function($params) {
  $dist = calc_distance($params[0], $params[1]);
  if($dist === false) throw ProcedureException('An Error occurred while calculating the distance.');
  return $dist;
}
?>
```