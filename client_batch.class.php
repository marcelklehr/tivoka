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
 * A JSON-RPC batch request
 * @package Tivoka
 */
class Tivoka_ClientRequestBatch extends Tivoka_ClientRequest
{
	/**
	 * @var array $id A list of the request objects to send as values and the ids as keys
	 * @access private
	 */
	public $id;
	
	/**
	 * @var array A list of the uparsed requests as an associative array
	 * @access private
	 */
	private $json;
	
	/**
	 * Initializes a new JSON-RPC batch request
	 *
	 * All values other than Tivoka_ClientRequest will be ignored
	 * @see Tivoka_ClientConnection::send()
	 * @param array $batch A list of requests to include, each a Tivoka_ClientRequest
	 */
	public function __construct(array $batch)
	{
		$this->id = array();
		
		//prepare requests...
		foreach($batch as $request)
		{
			//request...
			if($request instanceof Tivoka_ClientRequestRequest)
			{
				if(in_array($request->id,$this->id,true)) continue;
				
				$assoc = json_decode($request->getRequest(),TRUE);
				if($assoc == NULL) continue;
				
				$this->json[] = $assoc;
				$this->id[$request->id] = $request;
				continue;
			}
			//notification...
			if($request instanceof Tivoka_ClientRequestNotification)
			{
				$assoc = json_decode($request->getRequest(),TRUE);
				if($assoc == NULL) continue;
				
				$this->json[] = $assoc;
				continue;
			}
		}
	}
	
	public function processError($error)
	{
		return $this->mapProcessError($error);
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
			return new Tivoka_ClientResponse(NULL);
		}
		
		//no response?
		if(trim($response) == '')
		{
			return $this->mapProcessError(Tivoka_ClientResponse::ERROR_NO_RESPONSE,$response);
		}
		
		//decode
		$respassoc = json_decode($response,true);
		
		if($respassoc == NULL)
		{
			return $this->mapProcessError(Tivoka_ClientResponse::ERROR_INVALID_JSON,$response);
		}
		
		//validate
		if(count($respassoc) <= 1 || !is_array($respassoc))
		{
			return $this->mapProcessError(Tivoka_ClientResponse::ERROR_INVALID_RESPONSE,$response);
		}
		
		$ids = $this->id;
		//split..
		foreach($respassoc as $resp)
		{
			//no jsonrpc 				(not in one if, because if no array the keys wouldn't be found and ERROR)
			if(!isset($resp['jsonrpc']) && !isset($resp['id'])) return $this->mapProcessError(Tivoka_ClientResponse::ERROR_INVALID_RESPONSE,$response);
			
			if(!array_key_exists($resp['id'],$ids))//If the given id is not in the list...
			{
				if($resp['id'] != null) continue;
				
				$nullresps[] = $resp;
				continue;
			}
			
			//normal response...
			$responses[ $resp['id'] ] = $ids[ $resp['id'] ]->processResponse(json_encode($resp));
			unset($ids[ $resp['id'] ]);
		}
		
		//handle id:null responses...
		foreach($ids as $req)
		{
			$resp = array_shift($nullresps);
			$responses[ $req->id ] = $ids[ $req->id ]->processResponse(json_encode($resp));
		}
		return $responses;
	}
	
	/**
	 * Maps the error on each child request object, so the response can be returned as an array
	 * @return array An array of with Tivoka_ClientResponse objects as values and their ids as keys
	 */
	private function mapProcessError($error,$response=null)
	{
		$responses = array();
		foreach($this->id as $req)
		{
			$resp = new Tivoka_ClientResponse($response);
			$resp->process_error = $error;
			$responses[ $req->id ] = &$resp;
		}
		return $responses;
	}
}
?>