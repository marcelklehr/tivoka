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
 * JSON-RPC client
 * @package Tivoka
 */
class Tivoka_Client {
	
	/**
	 * Acts as a counter for the request IDs used
	 * @var integer
	 */
	public $id = 0;
	
	/**
	 * Holds the connection to the remote server
	 * @var Tivoka_Connection
	 */
	public $connection;
	
	/**
	 * Construct a native client
	 * @access private
	 * @param string $target URL
	 */
	public function __construct($target) {
		$this->connection = Tivoka::connect($target);
	}
	
	/**
	 * Sends a JSON-RPC request
	 * @param Tivoka_Request $request A Tivoka request
	 * @return void
	 */
	public function __call($method, $args) {
		$request = Tivoka::createRequest($this->id++, $method, $args);
		$this->connection->send($request);
		
		if($request->response->isError()) {
			throw new Tivoka_Exception($request->response->errorMessage, $request->response->error);
		}
		return $request->response->result;
	}

}
?>