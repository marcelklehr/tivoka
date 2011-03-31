<?php
class Tivoka_ClientRequestNotification extends Tivoka_ClientRequest
{
	public $id;
	private $json;
	
	public function __construct($method,$params=null)
	{
		$this->id = null;
		
		//prepare...
		$this->json = array(
			'jsonrpc'=>'2.0',
			'method'=>&$method
		);
		if($params !== null) $this->json['params'] = $params;
	}
	
	public function processError($error)
	{
		return;
	}
	
	public function getRequest()
	{
		return json_encode($this->json);
	}
	
	public function getResponse($response)
	{
		return;
	}
}
?>