<pre>
<?php
include('../include.php');

$target = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/server.php';
$request = Tivoka::createRequest('demo.substract', array(43,1));
$greeting = Tivoka::createRequest('demo.sayHello');

Tivoka::connect($target)->send($request, $greeting);


/*
 * Display the Results...
 */

if($request->isError()) var_dump($request->errorMessage);
else var_dump($request->result);// the result

	
if($greeting->isError()) var_dump($greeting->errorMessage);
else var_dump($greeting->result);// the result

?>
</pre>