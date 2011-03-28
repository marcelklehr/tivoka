<?php
	echo '<pre>';
	
	include('../tivoka.php');										//STEP 1
	
	$jsonrpc = new Tivoka_jsonRpcConnection('http://localhost/dev/jsonrpc/tivoka/sample/jsonrpcserver.php');//STEP 2
	
	$response = $jsonrpc->batch(array(													//STEP 3
		array('id'=>'65498','method'=>'demo.substract','params'=>array(43,1)),
		array('id'=>'65499','method'=>'demo.sayHello')
	));
	
	if($response->error())
	{
		var_dump($response->_processerror);
		var_dump($response->error);
	}else
	{
		if($response->result['65499']->error())
		{
			var_dump($response->result['65499']->_processerror);
			var_dump($response->result['65499']->error);
		}else
		{
			var_dump($response->result['65499']->result);
		}
		if($response->result['65498']->error())
		{
			var_dump($response->result['65498']->_processerror);
			var_dump($response->result['65498']->error);
		}else
		{
			var_dump($response->result['65498']->result);
		}			
	}
	
	echo '</pre>';
?>