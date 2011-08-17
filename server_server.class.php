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
 * Provides the methods of the given host object for invokation through the JSON-RPC protocol
 *
 * @package Tivoka
 */
class Tivoka_ServerServer
{
	/**
	 * @var object The object given to __construct()
	 * @see Tivoka_ServerServer::__construct()
	 * @access private
	 */
	public $host;
	
	/**
	 * @var array The parsed json input as an associative array
	 * @access private
	 */
	private $input;
	
	/**
	 * @var array A list of associative response arrays to json_encode
	 * @access private
	 */
	private $response;
	
	/**
	 * Initializes a Tivoka_ServerServer object
	 *
	 * @param object $host An object whose methods will be provided for invokation
	 * @param bool $hide_errors Pass TRUE for hiding all eventual erros to avoid messing up the response
	 */
	public function __construct($host, $hide_errors=FALSE)
	{
		//define some things...
		if($hide_errors != FALSE) error_reporting(0);//avoids messing up the response
		$this->host = &$host;
		$this->input = file_get_contents('php://input');
		$json_errors = array(
		    JSON_ERROR_NONE => '',
		    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		    JSON_ERROR_SYNTAX => 'Syntax error'
		);
	}
	
	/**
	 * Processes the HTTP input
	 *
	 * Notice: Calling this method will stop further execution of the script!
	 */
	public function process()
	{
		//set header if not already sent...
		if(headers_sent() === FALSE) header('Content-type: application/json');
		
		//validate input...
		
		//check existence...
		if(trim($this->input) === '')
		{
			$this->returnError(null,-32600);
			$this->respond();
		}
		
		//decode request...
		$this->input = json_decode($this->input,true);
		if($this->input === NULL)
		{
			$this->returnError(null,-32700, 'JSON: '.$json_errors[json_last_error()] );
			$this->respond();
		}
		
		//process batch...
		if(($batch = self::_parseBatch($this->input)) !== FALSE)
		{
			foreach($batch as $request)
			{
				new Tivoka_ServerProcessor($request,$this);
			}
			$this->respond();
		}
		
		//process request
		new Tivoka_ServerProcessor($this->input,$this);
		$this->respond();
	}
	
	/**
	 * Outputs the processed response
	 *
	 * @access private
	 */
	protected function respond()
	{
		if(!is_array($this->response))//no array
		{
			exit;
		}
		
		$count = count($this->response);
		if($count == 1)//single request
		{
			print json_encode($this->response[0]);
			exit;
		}
		
		if($count > 1)//batch request
		{
			print json_encode($this->response);
			exit;
		}
		
		if($count < 1)//no response
		{
			exit;
		}
	}
	
	/**
	 * Validates and sanitizes a normal request
	 *
	 * @param array $assoc The json-parsed JSON-RPC request
	 * @static
	 * @return array Returns tghe sanitized request and if it was invalid, a boolean FALSE is returned
	 * @access private
	 */
	public static function _parseRequest(array $assoc)
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
	public static function _parseNotification(array $assoc)
	{
		if(isset($assoc['jsonrpc'], $assoc['method']) === FALSE || isset($assoc['id']) !== FALSE)
			return FALSE;
		if($assoc['jsonrpc'] != '2.0')
			return FALSE;
		
		$request = array(
			'method' => &$assoc['method']
		);
		if(isset($assoc['params'])) $request['params'] = &$assoc['params'];
		
		return $request;
	}
	
	/**
	 * Validates a batch request
	 *
	 * @param array $assoc The json-parsed JSON-RPC request
	 * @static
	 * @return array Returns the original request and if it was invalid, a boolean FALSE is returned
	 * @access private
	 */
	public static function _parseBatch(array $assoc)
	{
		if($count = count($assoc) <= 1)
			return FALSE;
		/*
		$invalid = 0;
		foreach($assoc as $req)
		{
			if(self::_parseNotification($req) !== FALSE)
				continue;
			
			if(self::_parseRequest($req) !== FALSE)
				continue;
			$invalid++;
		}
		
		if($invalid >= $count) return FALSE;*/
		return $assoc;
	}
	
	/**
	 * Receives the computed result
	 *
	 * @param mixed $id The id of the original request
	 * @param mixed $result The computed result
	 * @access private
	 */
	public function returnResult(&$id,&$result)
	{
		$this->response[] = array(
				'jsonrpc' => '2.0',
				'id' => &$id,
				'result' => &$result
		);
	}
	
	/**
	 * Receives the error from computing the result
	 *
	 * @param mixed $id The id of the original request
	 * @param int $code The specified JSON-RPC error code
	 * @param mixed $data Additional data
	 * @access private
	 */
	public function returnError($id,$code,$data=null)
	{
		$msg = array(
			-32700 => 'Parse error',
			-32600 => 'Invalid Request',
			-32601 => 'Method not found',
			-32602 => 'Invalid params',
			-32603 => 'Internal error'
		);
		$this->response[] = array(
				'jsonrpc'=>'2.0',
				'id'=>&$id,
				'error'=> array(
					'code'=>&$code,
					'message'=>&$msg[$code],
					'data'=>&$data
		));
	}
}
?>