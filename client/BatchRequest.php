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
* A batch request
* @package Tivoka
*/
class Tivoka_BatchRequest extends Tivoka_Request
{
	/**
	 * Constructs a new JSON-RPC batch request
	 * All values other than Tivoka_ClientRequest will be ignored
	 * @param array $batch A list of requests to include, each a Tivoka_ClientRequest
	 * @see Tivoka_Client::send()
	 */
	public function __construct(array $batch)
	{
		if(Tivoka::$version == Tivoka::VER_1_0) throw new Tivoka_exception('Batch requests are not supported by JSON-RPC v1.0', Tivoka::ERR_SPEC_INCOMPATIBLE);
		$this->id = array();
	
		//prepare requests...
		foreach($batch as $request)
		{
			if(!($request instanceof Tivoka_Request) && !($request instanceof Tivoka_Notification))
				continue;
			
			//request...
			if($request instanceof Tivoka_Request)
			{
				if(in_array($request->id,$this->id,true)) continue;
				$this->id[$request->id] = $request;
			}
			
			$this->data[] = $request->data;
		}
		
		$this->response = new Tivoka_BatchResponse($this->id);
	}
}
?>