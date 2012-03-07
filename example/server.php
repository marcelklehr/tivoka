<?php
include('../include.php');

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

Tivoka::createServer($methods);
?>