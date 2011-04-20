<?php	
	/**
	 * STEP 1
	 * Load the Tivoka package
	 */
	include('../include.php');
	
	/**
	 * STEP 2
	 * Connect to a server
	 * (Here the examle server.php in the same directory)
	 */
	$jsonrpc = new Tivoka_ClientConnection('http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']).'/server.php');
	
	/**
	 * STEP 3
	 * Pack the Requests in a batch and send them right away
	 */
	$request1	= new Tivoka_ClientRequestRequest('65498','demo.substract',array(43,1));
	$request2	= new Tivoka_ClientRequestRequest('65499','demo.sayHello');
	$batch		= new Tivoka_ClientRequestBatch(array($request1,$request2));
	$response	= $jsonrpc->send($batch);
	
	
	/**
	 * Display the Results...
	 */
	
	$errors = array(
				Tivoka_ClientResponse::ERROR_NO_ERROR => '',
				Tivoka_ClientResponse::ERROR_NO_RESPONSE => 'No Response received!',
				Tivoka_ClientResponse::ERROR_INVALID_JSON => 'Invalid json response',
				Tivoka_ClientResponse::ERROR_INVALID_RESPONSE => 'Invalid JSON-RPC response',
				Tivoka_ClientResponse::ERROR_CONNECTION_FAILED => 'Connection failed'
	);
	
	echo '<pre>';
	
	/**
	 * Display reponse of request 65499
	 */
	if($response['65499']->isError())
	{
		//an error for request 65499
		var_dump($errors[$response['65499']->process_error]);
		var_dump($response['65499']->error);
		var_dump($response['65499']->response);
	}else
	{
		//the result for request 65499
		var_dump($response['65499']->result);
	}
	
	/**
	 * Display reponse of request 65498
	 */
	if($response['65498']->isError())
	{
		//an error for request 65499
		var_dump($errors[$response['65498']->process_error]);
		var_dump($response['65498']->error);
		var_dump($response['65498']->response);
	}else
	{
		//the result for request 65499
		var_dump($response['65498']->result);
	}
	
	echo '</pre>';
?>