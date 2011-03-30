<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <marcel.klehr@gmx.de>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * Validates the request and interacts between the server and the called method
 *
 * @package Tivoka
 */
class Tivoka_Processor
{
	/**
	 * @var Tivoka_Server Reference to the parent server object for returning the result/error
	 * @access private
	 */
	protected $server;
	
	/**
	 * @var array The parsed JSON-RPC request
	 * @see Tivoka_Processor::__construct()
	 * @access private
	 */
	protected $request;
	
	/**
	 * @var mixed The params as received through the JSON-RPC request
	 */
	public $params;
	
	/**
	 * Initializes a Tivoka_Processor object
	 *
	 * @param array $request The parsed JSON-RPC request
	 * @param Tivoka_Server $server The parent server object
	 * @access private
	 */
	public function __construct(array $request,Tivoka_Server &$server)
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
	
	/**
	 * Receives the computed result
	 *
	 * @param mixed $result The computed result
	 */
	public function returnResult($result)
	{
		if(!Tivoka_Server::_is('request',$this->request)) return;
		$this->server->returnResult($this->request['id'],$result);
	}
	
	/**
	 * Receives the error from computing the result
	 *
	 * @param int $code The specified JSON-RPC error code
	 * @param mixed $data Additional data
	 */
	public function returnError($code,$data=null)
	{
		if(!Tivoka_Server::_is('request',$this->request)) return;
		
		$id = (!isset($this->request['id'])) ? null : $this->request['id'];
		$this->server->returnError($id,$code,$data);
	}
	

}
?>