<?php
/**
 *	Tivoka - a JSON-RPC implementation for PHP
 *	Copyright (C) 2011  Marcel Klehr <marcel.klehr@gmx.de>
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
 * @author Marcel Klehr <marcel.klehr@gmx.de>
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
	
	/**
	 * Checks whether the given response is a valid result
	 * @param array $assoc The parsed JSON-RPC response as an associative array
	 * @param mixed $id The id of the original request
	 * @return bool
	 */
	protected static function _isResult(array $assoc,$id)
	{
		if(isset($assoc['jsonrpc'], $assoc['result']))
			return ($assoc['id'] == $id || !isset($assoc['id']) AND $assoc['jsonrpc'] == '2.0');
		return FALSE;
	}
	
	/**
	 * Checks whether the given response is a valid error
	 * @param array $assoc The parsed JSON-RPC response as an associative array
	 * @param mixed $id The id of the original request
	 * @return bool
	 */
	protected static function _isError(array $assoc,$id)
	{
		if(isset($assoc['jsonrpc'], $assoc['error']))
			return ($assoc['id'] == $id || !isset($assoc['id']) AND $assoc['jsonrpc'] == '2.0');
		return FALSE;		
	}
}
?>