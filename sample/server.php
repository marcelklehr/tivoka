<?php
/**
 * STEP 1
 * Load the Tivoka package
 */
include('../include.php');

/**
 * STEP 2
 * Define an array of remote methods
 */
$methods = array(

	'demo.sayHello' => function($request)
	{
		$request->returnResult('Hello World!');
	},
	
	'demo.substract' => function($request)
	{
		$tmp = array_keys($request->params);
		if(	!is_array($request->params) ||
			!count($request->params) == 2 || 
			!is_numeric($request->params[array_pop($tmp)]) ||
			!is_numeric($request->params[array_pop($tmp)]) )
		{
			$request->returnError(-32602);return False;
		}
		$request->returnResult($request->params[0] - $request->params[1]);
		return TRUE;
	}
);

/**
 * STEP 3
 * Implement the methods
 */
$proxy = new Tivoka_ServerArrayHost($method);

/**
 * STEP 4
 * Init the server and process the request
 */
$server = new Tivoka_ServerServer($proxy);
$server->process();
?>