<?php
class Tivoka_ClientRequestRequest extends Tivoka_ClientRequest
{
	public $id;
	private $json;
	
	public function __construct($id,$method,$params=null)
	{
		$this->id = $id;
		
		//prepare...
		$this->json = array(
			'jsonrpc'=>'2.0',
			'method'=>&$method,
			'id'=>&$id
			);
		if($params !== null) $this->json['params'] = &$params;
	}
	
	public function processError($error)
	{
		$resp = new Tivoka_ClientResponse();
		$resp->process_error = &$error;
		return $resp;
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
			return new Tivoka_ClientResponse();
		}
		
		//no response?
		if(trim($response) == '')
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->process_error = Tivoka_ClientResponse::ERROR_NO_RESPONSE;
			return $resp;
		}
		
		//decode
		$respassoc = json_decode($response,true);
		
		if($respassoc == NULL)
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->process_error = Tivoka_ClientResponse::ERROR_INVALID_JSON;
			return $resp;
		}
		
		//server error?
		if(self::_isError($respassoc,$this->id))
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->error['msg'] = $respassoc['error']['message'];
			$resp->error['code'] = $respassoc['error']['code'];
			$resp->error['data'] = $respassoc['error']['data'];
			return $resp;
		}
		
		//valid result?
		if(self::_isResult($respassoc,$this->id))
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->result = $respassoc['result'];
			return $resp;	
		}
		
		$resp = new Tivoka_ClientResponse($response);
		$resp->process_error = Tivoka_ClientResponse::ERROR_INVALID_RESPONSE;
		return $resp;
	}
}
?>