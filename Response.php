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
* The  response to a JSON-RPC request
* @package Tivoka
*/
class Tivoka_Response
{
	public $result;
	public $error;
	public $internal_error = 0;
	public $data;
	
	public $request;
	
	function __construct(&$request) {
		$this->request = &$request;
	}
	
	/**
	 * Sets an internal error
	 * @param int $error
	 * @return void
	 */
	public function setError($error)
	{
		$this->internal_error = $error;
	}
	
	/**
	 * Interprets the response
	 * @param string $response json data
	 * @return void
	 */
	public function setResponse($response)
	{
		//process error?
		if($response === FALSE)
		{
			return;
		}
		
		$this->data = $response;
	
		//no response?
		if(trim($response) == '')
		{
			$this->internal_error = Tivoka::ERR_NO_RESPONSE;
			return;
		}
	
		//decode
		$resparr = json_decode($response,true);
		$this->interpretResponse($resparr);
	}
	
	/**
	 * Interprets the parsed response
	 * @param array $resparr
	 */
	protected function interpretResponse(array $resparr)
	{
		if($resparr == NULL)
		{
			$resp->internal_error = Tivoka::ERR_INVALID_JSON;
			return;
		}
		
		//server error?
		if(($error = self::interpretError($resparr, $this->request->id)) !== FALSE)
		{
			$this->error['msg'] = $error['error']['message'];
			$this->error['code'] = $error['error']['code'];
			$this->error['data'] = $error['error']['data'];
			return;
		}
		
		//valid result?
		if(($result = self::interpretResult($resparr, $this->request->id)) !== FALSE)
		{
			$this->result = $result['result'];
			return;
		}
	
		$this->internal_error = Tivoka::ERR_INVALID_RESPONSE;;
	}
	
	/**
	 * Determines whether an error occured
	 * @return bool
	 */
	public function isError()
	{
		if($this->internal_error !== 0 || $this->error != NULL) return TRUE;
		return FALSE;
	}
	
	/**
	* Checks whether the given response is a valid result
	* @param array $assoc The parsed JSON-RPC response as an associative array
	* @param mixed $id The id of the original request
	* @return array the parsed JSON object
	*/
	protected static function interpretResult(array $assoc,$id)
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
	 * @return array parsed JSON object
	 */
	protected static function interpretError(array $assoc, $id)
	{
		if(isset($assoc['jsonrpc'], $assoc['error']) == FALSE)
			return FALSE;
		
		if($assoc['id'] != $id && $assoc['id'] != null && isset($assoc['id']) OR $assoc['jsonrpc'] != '2.0')
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