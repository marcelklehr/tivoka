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
 * A single JSON-RPC notification
 * @package Tivoka
 */
class Tivoka_ClientRequestNotification extends Tivoka_ClientRequest
{
	/**
	 * @var null
	 */
	public $id;
	
	/**
	 * @var array The unparsed json data as an associative array
	 * @access private
	 */
	private $json;
	
	/**
	 * Initializes a new JSON-RPC notification
	 * 
	 * Note: You won't get any response for this so try avoiding the use of this request type
	 * @see Tivoka_ClientConnection::send()
	 * @param string $method The remote procedure to invoke
	 * @param mixed $params Additional params for the remote procedure
	 */
	public function __construct($method,$params=null)
	{
		$this->id = null;
		
		//prepare...
		$this->json = self::_prepareRequest($method, $params);
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
		return new Tivoka_ClientResponse($response);
	}
	
	
	/**
	 * Prepares the request
	 * @param string $method The method to be called
	 * @param mixed $params Additional parameters
	 * @return mixed Returns the prepared assotiative array to encode
	 */
	protected static function _prepareRequest($method,$params=null)
	{
		return array(
			'jsonrpc'=>'2.0',
			'method'=>&$method
		);
		if($params !== null) $this->json['params'] = $params;
	}
	
}
?>