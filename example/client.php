<pre>
<?php
/*
 * STEP 1
 * Load the Tivoka package
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
 * (Here the examle server.php in the same directory)
 */
$target = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/server.php';

Tivoka_Client::connect($target)->send($request);


/*
 * Display the Results...
 */

// Describes the possible errors
$errors = array(
	Tivoka::ERR_NO_ERROR => '',
	Tivoka::ERR_NO_RESPONSE => 'No Response received!',
	Tivoka::ERR_INVALID_JSON => 'Invalid JSON response',
	Tivoka::ERR_INVALID_RESPONSE => 'Invalid JSON-RPC response',
	Tivoka::ERR_CONNECTION_FAILED => 'Connection failed'
);

/*
 * Display reponse
 */
if($request->response->isError())
{
	// an error occured
	var_dump( $errors[ $request1->response->internal_error ] );
	var_dump($request->response->error);
	var_dump($request->response->data);
}else
{
	// the result
	var_dump($request->response->result);
}
?>
</pre>