<?php
/**
 * STEP 1
 * Load the Tivoka package
 */
include('../server.inc.php');

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
		if(!is_array($request->params)) { $request->returnError(-32602); return FALSE;}
		$tmp = array_keys($request->params);
		if(	!count($request->params) == 2 || 
			!is_numeric($request->params[array_pop($tmp)]) ||
			!is_numeric($request->params[array_pop($tmp)]) )
		{
			$request->returnError(-32602);return False;
		}
		$request->returnResult(intval($request->params[0]) - intval($request->params[1]));
		return TRUE;
	}
);

/**
 * STEP 3
 * Implement the methods
 */
$methodhost = new Tivoka_ArrayObject($methods);

/**
 * STEP 4
 * Init the server and process the request
 */
Tivoka_Server::start($methodhost);
?>