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
 * Opens a connection to the given JSONJ-RPC server for invoking the provided remote procedures
 * @package Tivoka
 */
class Tivoka_ClientConnection
{
	/**
	 * @var array The target
	 */
	public $target;
	
	/**
	 * Initializes a Tivoka_ClientConnection object
	 * @param string $target the URL of the target server (MUST include http scheme)
	 * @throws Tivoka_InvalidTargetException
	 */
	public function __construct($target)
	{
		//validate url...
		if(!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
			throw new Tivoka_InvalidTargetException('Valid URL (scheme,domain[,path][,file]) required.', 1);
		
		//validate scheme...
		$t = parse_url($target);
		if($t['scheme'] !== 'http')
			throw new Tivoka_InvalidTargetException('Unknown or unsupported scheme given.', 2);

		$this->target = $target;
	}
	
	/**
	 * Sends a JSON-RPC request to the defined target
	 * @param Tivoka_ClientRequest $request A Tivoka request
	 * @see Tivoka_ClientResponse
	 * @return mixed Depends on the given request object
	 */
	public function send(Tivoka_ClientRequest $request)
	{
		$json = $request->getRequest();
		
		//preparing...
		$context = stream_context_create(array(
			'http' => array(
				'content' => $json,
				'header' => "Content-Type: application/json\r\n".
							"Connection: Close\r\n",
				'method' => 'post',
				'protocol_version' => 1.1,
				'timeout' => 10.0
			)
		));
		
		//sending...
		$response = @file_get_contents($this->target,false,$context);
		if($response === FALSE)
		{
			return $request->processError(Tivoka_ClientResponse::ERROR_CONNECTION_FAILED);
		}
		
		return $request->processResponse($response);
	}
}
?>