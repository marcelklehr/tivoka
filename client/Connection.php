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
class Tivoka_Connection {
	
	/**
	 * Constructs connection
	 * @access private
	 * @param string $target URL
	 */
	public function __construct($target) {
		$this->target = $target;
	}
	
	/**
	 * Sends a JSON-RPC request
	 * @param Tivoka_Request $request A Tivoka request
	 * @return void
	 */
	public function send($request) {
		if(func_num_args() > 1 ) $request = func_get_args();
		if(is_array($request)) {
			$request = Tivoka::createBatch($request);
		}
		
		if(!($request instanceof Tivoka_Request)) throw new Tivoka_Exception('Invalid data type to be sent to server');
		
		// preparing connection...
		$context = stream_context_create(array(
				'http' => array(
					'content' => (string) $request,
					'header' => "Content-Type: application/json\r\n".
								"Connection: Close\r\n",
					'method' => 'post',
					'timeout' => 10.0
		)
		));
	
		//sending...
		$response = @file_get_contents($this->target, false, $context);
		if($response === FALSE) {
			throw new Tivoka_Exception('Connection Failed', Tivoka::ERR_CONNECTION_FAILED);
		}
		
		$request->response->setResponse($response);
	}
	
	public function sendRequest($id, $method, $params=null) {
		$request = Tivoka::createRequest($id, $method, $params);
		$this->send($request);
		return $request->response;
	}
	
	public function sendNotification($method, $params=null) {
		$this->send(Tivoka::createNotification($method, $params));
	}
}
?>