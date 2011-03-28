<?php
include('../server.class.php');	//STEP 1

$server = array(
	"demo.sayHello" => function($request)
	{
		$request->result("Hello World!");
	},
	"demo.substract" => function($request)
	{
		$tmp = array_keys($request->params);
		if(	!is_array($request->params) ||
			!count($request->params) == 2 || 
			!is_numeric($request->params[array_pop($tmp)]) ||
			!is_numeric($request->params[array_pop($tmp)]) )
		{
			$request->error(-32602);
		}
		$request->result($request->params[0] - $request->params[1]);
		return TRUE;
	}
);


new Tivoka_jsonRpcServer(new Tivoka_jsonRpcArrayHost($server));		//STEP 3

?>



