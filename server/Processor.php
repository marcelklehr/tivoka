<?php
/**
*	Tivoka - A simple and easy-to-use client and server implementation of JSON-RC
*	Copyright (C) 2011  Marcel Klehr <m.klehr@gmx.net>
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
* @author Marcel Klehr <mklehr@gmx.net>
* @copyright (c) 2011, Marcel Klehr
*/
/**
* Processes a single request on the server
* @package Tivoka
*/
class Tivoka_Processor
{
	
	/**
	* @var Tivoka_ServerServer Reference to the parent server object for returning the result/error
	* @access private
	*/
	public $server;
	
	/**
	 * @var array The parsed JSON-RPC request
	 * @see Tivoka_ServerProcessor::__construct()
	 * @access private
	 */
	public $request;
	
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
	public function __construct(array $request,Tivoka_Server $server)
	{
		$this->server = $server;
		$this->request = array();
		$this->params = (isset($request['params']) === FALSE) ? null : $request['params'];
	
		//validate...
		if(($req = self::interpretRequest($request)) !== FALSE)
		{
			$this->request = $req;
		}
	
		if(($req = self::interpretNotification($request)) !== FALSE)
		{
			$this->request = $req;
		}
	
		if($this->request === array())
		{
			$this->returnError(-32600, $request);
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
		if(self::interpretNotification($this->request) !== FALSE) return TRUE;
		$this->server->returnResult($this->request['id'],$result);
		return TRUE;
	}
	
	/**
	 * Receives the error from computing the result
	 *
	 * @param int $code The specified JSON-RPC error code
	 * @param mixed $data Additional data
	 */
	public function returnError($code,$data=null)
	{
		if(self::interpretNotification($this->request) !== FALSE) return FALSE;
		
		$id = (isset($this->request['id']) === FALSE) ? null : $this->request['id'];
		$this->server->returnError($id,$code,$data);
		return FAlSE;
	}
	
	/**
	 * Validates and sanitizes a normal request
	 *
	 * @param array $assoc The json-parsed JSON-RPC request
	 * @static
	 * @return array Returns tghe sanitized request and if it was invalid, a boolean FALSE is returned
	 * @access private
	 */
	public static function interpretRequest(array $assoc)
	{
		if(isset($assoc['jsonrpc'], $assoc['id'], $assoc['method']) === FALSE)
		return FALSE;
		if($assoc['jsonrpc'] != '2.0')
		return FALSE;
	
		$request = array(
					'id' =>  &$assoc['id'],
					'method' => &$assoc['method']
		);
		if(isset($assoc['params'])) $request['params'] = &$assoc['params'];
	
		return $request;
	}
	
	/**
	 * Validates and sanitizes a notification
	 *
	 * @param array $assoc The json-parsed JSON-RPC request
	 * @static
	 * @return array Returns the sanitized request and if it was invalid, a boolean FALSE is returned
	 * @access private
	 */
	public static function interpretNotification(array $assoc)
	{
		if(isset($assoc['jsonrpc'], $assoc['method']) === FALSE || isset($assoc['id']) !== FALSE)
		return FALSE;
		if($assoc['jsonrpc'] != '2.0')
		return FALSE;
	
		$request = array(
					'method' => &$assoc['method']
		);
		if(isset($assoc['params'])) $request['params'] = $assoc['params'];
	
		return $request;
	}
}
?>