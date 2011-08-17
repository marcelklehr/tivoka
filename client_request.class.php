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
 * A single JSON-RPC request
 * @package Tivoka
 */
class Tivoka_ClientRequestRequest extends Tivoka_ClientRequest
{
	/**
	 * @var mixed The id of the request
	 */
	public $id;
	
	/**
	 * @var array The unparsed json data as an associative array
	 * @access private
	 */
	private $json;
	
	/**
	 * Initializes a new JSON-RPC request
	 * @see Tivoka_ClientConnection::send()
	 * @param mixed $id The id of the request
	 * @param string $method The remote procedure to invoke
	 * @param mixed $params Additional params for the remote procedure
	 */
	public function __construct($id,$method,$params=null)
	{
		$this->id = $id;
		
		//prepare...
		$this->json = self::_prepareRequest($id, $method, $params);
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
	
	public function processResponse($response)
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
		if(($error = self::_parseError($respassoc,$this->id)) !== FALSE)
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->error['msg'] = $error['error']['message'];
			$resp->error['code'] = $error['error']['code'];
			$resp->error['data'] = $error['error']['data'];
			return $resp;
		}
		
		//valid result?
		if(($result = self::_parseResult($respassoc,$this->id)) !== FALSE)
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->result = $result['result'];
			return $resp;
		}
		
		$resp = new Tivoka_ClientResponse($response);
		$resp->process_error = Tivoka_ClientResponse::ERROR_INVALID_RESPONSE;
		return $resp;
	}
	
	
	/**
	 * Prepares the request
	 * @param mixed $id The id of the original request
	 * @param string $method The method to be called
	 * @param mixed $params Additional parameters
	 * @return mixed Returns the prepared assotiative array to encode
	 */
	protected static function _prepareRequest($id, $method, $params=null)
	{
		$request = array(
			'jsonrpc' => '2.0',
			'id' => $id,
			'method' => $method,
		);
		
		if($params !== null) $request['params'] = $params;
		return $request;
	}
	
	/**
	 * Checks whether the given response is a valid result
	 * @param array $assoc The parsed JSON-RPC response as an associative array
	 * @param mixed $id The id of the original request
	 * @return mixed Returns the parsed JSON object
	 */
	protected static function _parseResult(array $assoc,$id)
	{
		if(isset($assoc['jsonrpc'], $assoc['result']) === FALSE)
			return FALSE;
		if($assoc['id'] !== $id && isset($assoc['id']) OR $assoc['jsonrpc'] != '2.0')
			return FALSE;
		
		return array(
			'id' => $assoc['id'],
			'result' => $assoc['result']
		);	
	}
	
	/**
	 * Checks whether the given response is valid and an error
	 * @param array $assoc The parsed JSON-RPC response as an associative array
	 * @param mixed $id The id of the original request
	 * @return mixed Returns the parsed JSON object
	 */
	protected static function _parseError(array $assoc, $id)
	{
		if(isset($assoc['jsonrpc'], $assoc['error']) == FALSE)
			return FALSE;
			
		if($assoc['id'] != $id && $assoc['id'] != null AND isset($assoc['id']) OR $assoc['jsonrpc'] != '2.0')
			return FALSE;
		
		if(isset($assoc['error']['message'], $assoc['error']['code']) === FALSE)
			return FALSE;
		
		return array(
			'id' => $assoc['id'],
			'error' => $assoc['error']
		);	
	}
}
?>