<?php
/*
 * Tivoka_Processor
 * Validates the request and interacts between the server and the called method
 *
 * @method public __construct($request,&$server)
 *		@param string $request The plain json encoded request
 *		@param Tivoka_Server $server Reference to the server for returning the result/error
 * @method public result($result)
 *		@param mixed $result The result of the request given with __construct
 * @method public error($code,$data='')
 *		@param integer $code The code of the error which occured processing the request given with __construct
 *		@param mixed $data The more information about the error
 */
class Tivoka_Processor
{
	protected $server;
	protected $request;
	public $params;
	
	public function __construct($request,Tivoka_Server &$server)
	{
		$this->server = &$server;
		$this->request = &$request;
		$this->params = (isset($this->request['params'])) ? $this->request['params'] : null;
		
		//validate...
		if(!Tivoka_Server::_is('request',$this->request) && !Tivoka_Server::_is('notification',$this->request))
		{
			$this->error(-32600);
			return;
		}
		
		//search method...
		if(!is_callable(array($this->server->host,$this->request['method'])))
		{
			$this->error(-32601);
			return;
		}
		
		//invoke...
		$this->server->host->{$this->request['method']}($this);
	}
	
	//callbacks...
	
	public function error($code,$data=null)
	{
		if(!Tivoka_Server::_is('request',$this->request)) return;
		
		$id = (!isset($this->request['id'])) ? null : $this->request['id'];
		$this->server->error($id,$code,$data);
	}
	
	public function result($result)
	{
		if(!Tivoka_Server::_is('request',$this->request)) return;
		$this->server->result($this->request['id'],$result);
	}
}
?>