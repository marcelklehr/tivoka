<?php
	echo '<pre>';
	
	include('../tivoka.php');																			//STEP 1
	
	$jsonrpc = new Tivoka_Connection('http://localhost/dev/tivoka/sample/jsonrpcserver.php');	//STEP 2
	
	$response = $jsonrpc->sendBatch(array(																	//STEP 3
		array('id'=>'65498','method'=>'demo.substract','params'=>array(43,1)),
		array('id'=>'65499','method'=>'demo.sayHello')
	));
	
	if($response->isError()) //an error occured for the whole request
	{
		var_dump($response->process_error);
		var_dump($response->error);
	}else					//no error so far
	{
		if($response->result['65499']->isError()) //an error for request 65499?
		{
			var_dump($response->result['65499']->process_error);
			var_dump($response->result['65499']->error);
		}else
		{
			var_dump($response->result['65499']->result);
		}
		if($response->result['65498']->isError()) //an error for request 65499?
		{
			var_dump($response->result['65498']->process_error);
			var_dump($response->result['65498']->error);
		}else
		{
			var_dump($response->result['65498']->result);
		}			
	}
	
	echo '</pre>';
?>