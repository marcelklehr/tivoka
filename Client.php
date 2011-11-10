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
class Tivoka_Client
{
	/**
	* Initializes a Tivoka_ClientConnection object
	* @param string $target the URL of the target server (MUST include http scheme)
	* @throws Tivoka_Exception
	*/
	static function connect($target)
	{
		//validate url...
		if(!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
		throw new Tivoka_Exception('Valid URL (scheme,domain[,path][,file]) required.', 1);
		
		//validate scheme...
		$t = parse_url($target);
		if($t['scheme'] !== 'http')
		throw new Tivoka_Exception('Unknown or unsupported scheme given.', 2);
		
		return new Tivoka_Client($target);
	}
	
	/**
	 * Constructs client
	 * @access private
	 * @param string $target URL
	 */
	private function __construct($target)
	{
		$this->target = $target;
	}
	
	/**
	* Sends a JSON-RPC request to the defined with connect
	* @param Tivoka_Request $request A Tivoka request
	* @return void
	*/
	public function send(Tivoka_Request $request)
	{
		//preparing...
		$context = stream_context_create(array(
				'http' => array(
					'content' => $request->getData(),
					'header' => "Content-Type: application/json\r\n".
								"Connection: Close\r\n",
					'method' => 'post',
					'timeout' => 10.0
		)
		));
	
		//sending...
		$response = @file_get_contents($this->target, false, $context);
		if($response === FALSE)
		{
			return $request->response->setError(Tivoka::ERR_CONNECTION_FAILED);
		}
		
		$request->response->setResponse($response);
	}
}
?>