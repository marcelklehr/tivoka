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
* A JSON-RPc request
* @package Tivoka
*/
class Tivoka_Request
{
	public $id;
	public $data;
	public $response;
	
	/**
	 * Constructs a new JSON-RPC request object
	 * @param mixed $id The id of the request
	 * @param string $method The remote procedure to invoke
	 * @param mixed $params Additional params for the remote procedure (optional)
	 * @see Tivoka_Connection::send()
	 */
	public function __construct($id,$method,$params=null) {
		$this->id = $id;
		$this->response = new Tivoka_Response($this);
	
		//prepare...
		$this->data = self::prepareRequest($id, $method, $params);
	}
	
	/**
	 * Send this request to a remote server 
	 * @param string $target The URL of the remote server
	 */
	public function send($target) {
		Tivoka::connect($target)->send($this);
	}
	
	/**
	 * Pack the request data with json encoding
	 * @return string the json encoded request
	 */
	public function __toString() {
		return json_encode($this->data);
	}
	
	/**
	 * Encodes the request properties
	 * @param mixed $id The id of the request
	 * @param string $method The method to be called
	 * @param mixed $params Additional parameters
	 * @return mixed Returns the prepared assotiative array to encode
	 */
	protected static function prepareRequest($id, $method, $params=null) {
		switch(Tivoka::$version) {
		case Tivoka::VER_2_0:
			$request = array(
					'jsonrpc' => '2.0',
					'method' => $method,
			);
			if($id !== null) $request['id'] = $id;
			if($params !== null) $request['params'] = $params;
			return $request;
		case Tivoka::VER_1_0:
			$request = array(
				'method' => $method,
				'id' => $id
			);
			if($params !== null) {
				if((bool)count(array_filter(array_keys($params), 'is_string'))) throw new Tivoka_Exception('JSON-RC 1.0 doesn\'t allow for named parameters');
				$request['params'] = $params;
			}
			return $request;
		}
	}
}
?>