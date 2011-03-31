<?php
class Tivoka_BatchRequest extends Tivoka_Request
{
	public $id;
	private $json;
	
	public function __construct(array $batch)
	{
		$this->id = array();
		
		//prepare requests...
		foreach($batch as $request)
		{
			//request...
			if($request instanceof Tivoka_RequestRequest)
			{
				if(in_array($request->id,$this->id,true)) continue;
				
				$assoc = json_decode($request->getRequest(),TRUE);
				if($assoc == NULL) continue;
				
				$this->json[] = $assoc;
				$this->id[$request->id] = $request;
				continue;
			}
			//notification...
			if($request instanceof Tivoka_NotificationRequest)
			{
				$assoc = json_decode($request->getRequest(),TRUE);
				if($assoc == NULL) continue;
				
				$this->json[] = $assoc;
				continue;
			}
			if(($request instanceof Tivoka_Request) == FALSE) throw new InvalidArgumentException('Expected parameter 1 to be list of Tivoka_Request objects');
		}
	}
	
	public function processError($error)
	{
		return $this->mapProcessError($error);
	}
	
	public function getRequest()
	{
		return json_encode($this->json);
	}
	
	public function getResponse($response)
	{
		//process error?
		if($response === FALSE)
		{
			return new Tivoka_Response(NULL);
		}
		
		//no response?
		if(trim($response) == '')
		{
			return $this->mapProcessError(Tivoka_Response::ERROR_NO_RESPONSE,$response);
		}
		
		//decode
		$respassoc = json_decode($response,true);
		
		if($respassoc == NULL)
		{
			return $this->mapProcessError(Tivoka_Response::ERROR_INVALID_JSON,$response);
		}
		
		//validate
		if(count($respassoc) <= 1 || !is_array($respassoc))
		{
			return $this->mapProcessError(Tivoka_Response::ERROR_INVALID_RESPONSE,$response);
		}
		
		$ids = $this->id;
		//split..
		foreach($respassoc as $resp)
		{
			//no jsonrpc 				(not in one if, because if no array the keys wouldn't be found and ERROR)
			if(!isset($resp['jsonrpc']) && !isset($resp['id'])) return $this->mapProcessError(Tivoka_Response::ERROR_INVALID_RESPONSE,$response);
			
			if(!array_key_exists($resp['id'],$ids))//If the given id is not in the list...
			{
				if($resp['id'] != null) continue;
				
				$nullresps[] = $resp;
				continue;
			}
			
			//normal response...
			$responses[ $resp['id'] ] = $ids[ $resp['id'] ]->getResponse(json_encode($resp));
			unset($ids[ $resp['id'] ]);
		}
		
		//handle id:null responses...
		foreach($ids as $req)
		{
			$resp = array_shift($nullresps);
			$responses[ $req->id ] = $ids[ $req->id ]->getResponse(json_encode($resp));
		}
		return $responses;
	}
	
	private function mapProcessError($error,$response=null)
	{
		$responses = array();
		foreach($this->id as $req)
		{
			$resp = new Tivoka_Response($response);
			$resp->process_error = $error;
			$responses[ $req->id ] = &$resp;
		}
		return $responses;
	}
}
?>