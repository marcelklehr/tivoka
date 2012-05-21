<?php
include('../include.php');
use Tivoka\Server as JsonRpc;

$methods = array(
	'demo.sayHello' => function($request)
	{
		$request->result('Hello World!');
	},
	
	'demo.substract' => function($request)
	{
		if(!is_array($request->params)) return $request->error(-32602);
		return $request->result(intval($request->params[0]) - intval($request->params[1]));
	}
);

Tivoka\Server::provide($methods)->dispatch();
?>