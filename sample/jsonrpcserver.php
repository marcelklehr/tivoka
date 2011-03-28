<?php
include('../tivoka.php');	//STEP 1

//define the remote procedures in an array
$server = array(							//STEP 2
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
			$request->returnError(-32602);
		}
		$request->returnResult($request->params[0] - $request->params[1]);
		return TRUE;
	}
);

//convert the array and process the request
$server = new Tivoka_Server(new Tivoka_ArrayHost($server));		//STEP 3
$server->process();

?>