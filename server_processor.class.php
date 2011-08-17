<?php
/**
 *	Tivoka - a JSON-RPC implementation for PHP
 *	Copyright (C) 2011  Marcel Klehr
 *
 *	This program is free software; you can redistribute it and/or modify it under the 
 *	terms of the GNU General Public License as published by the Free Software Foundation;
 *	either version 3 of the License, or (at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 *	without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *	See the GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License along with this program;
 *	if not, see <http://www.gnu.org/licenses/>.
 *
 * @package Tivoka
 * @author Marcel Klehr
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * Validates the request and interacts between the server and the called method
 *
 * @package Tivoka
 */
class Tivoka_ServerProcessor
{
	/**
	 * @var Tivoka_ServerServer Reference to the parent server object for returning the result/error
	 * @access private
	 */
	protected $server;
	
	/**
	 * @var array The parsed JSON-RPC request
	 * @see Tivoka_ServerProcessor::__construct()
	 * @access private
	 */
	protected $request;
	
	/**
	 * @var mixed The params as received through the JSON-RPC request
	 */
	public $params;
	
	/**
	 * Initializes a Tivoka_ServerProcessor object
	 *
	 * @param array $request The parsed JSON-RPC request
	 * @param Tivoka_ServerServer $server The parent server object
	 * @access private
	 */
	public function __construct(array $request,Tivoka_ServerServer &$server)
	{
		$this->server = &$server;
		$this->request = array();
		$this->params = (isset($request['params']) === FALSE) ? null : $request['params'];
		
		//validate...
		if(($req = Tivoka_ServerServer::_parseRequest($request)) !== FALSE)
		{
			$this->request = $req;
		}
		
		if(($req = Tivoka_ServerServer::_parseNotification($request)) !== FALSE)
		{
			$this->request = $req;
		}
		
		if($this->request === array())
		{
			$this->returnError(-32600);
			return;
		}
		
		//search method...
		if(!is_callable(array($this->server->host,$this->request['method'])))
		{
			$this->returnError(-32601);
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
		if(Tivoka_ServerServer::_parseNotification($this->request) !== FALSE) return;
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
		if(Tivoka_ServerServer::_parseNotification($this->request) !== FALSE) return;
		
		$id = (isset($this->request['id']) === FALSE) ? null : $this->request['id'];
		$this->server->returnError($id,$code,$data);
	}
	

}
?>