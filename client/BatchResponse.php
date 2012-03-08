<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * The response to a batch request
 * @package Tivoka
 */
class Tivoka_BatchResponse extends Tivoka_Response
{	
	/**
	 * Interprets the parsed response
	 * @param array $resparr json data
	 * @return void
	 */
	protected function interpretResponse($resparr) {
		if($resparr == NULL)
		{
			throw new Tivoka_Exception('Received response couldn\'t be parsed as JSON.', Tivoka::ERR_INVALID_JSON);
		}
	
		//validate
		if(count($resparr) < 1 || !is_array($resparr))
		{
			throw new Tivoka_Exception('Expected batch response, but none was received', Tivoka::ERR_INVALID_RESPONSE);
		}
	
		$requests = $this->request;
		$nullresps = array();
		$responses = array();
		
		//split..
		foreach($resparr as $resp)
		{
			if(!is_array($resp)) throw new Tivoka_Exception('Expected batch response, but no array was received', Tivoka::ERR_INVALID_RESPONSE);
			
			//is jsonrpc prtocol?
			if(!isset($resp['jsonrpc']) && !isset($resp['id'])) throw new Tivoka_Exception('The received reponse doesn\'t implement the JSON-RPC prototcol.', Tivoka::ERR_INVALID_RESPONSE);
			
			//responds to an existing request?
			if(!array_key_exists($resp['id'],$requests))
			{
				if($resp['id'] != null) continue;
				
				$nullresps[] = $resp;
				continue;
			}
				
			//normal response...
			$requests[ $resp['id'] ]->response->set(json_encode($resp));
			unset($requests[ $resp['id'] ]);
		}
	
		//handle id:null responses...
		foreach($requests as $req)
		{
			$resp = array_shift($nullresps);
			$requests[ $req->id ]->response->setResponse(json_encode($resp));
		}
	}
}
?>