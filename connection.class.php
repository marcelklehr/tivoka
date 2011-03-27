<?php
/*
 *	Tivoka - a JSON-RPC implementation for PHP
 *	Copyright (C) 2011  Marcel Klehr (marcel.klehr@gmx.de)
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
 */

/*
 * Tivoka_jsonRpcConnection
 * Opens a connection to the given JSONJ-RPC server for invoking the provided remote procedures
 *
 * @method public __construct($server_addr)
 *		@param string $server_addr The http address of the server to connect to
 * @method public batch($batch)
 *		@param array $batch An array of request items which must contain the keys: "method", "id"(optional), "params"(optional)
 *		Notice: Only omit "id" if you want the request to be treated as a notification and thus don't expect to get a response. See the JSON-RPC specification for more details.
 * @method public request($id,$method,$params="")
 *		@param mixed $id A unique value to associate the request with its response
 *		@param string $method The method to invoke
 *		@param mixed $params Additional parameters for the method
 * @method public notification($method,$params="")
 *		@param string $method The method to invoke
 *		@param mixed $params Additional parameters for the method
 * @method public _request($json,$id="")
 *		@param string $json The plain json encoded request to send to the server
 *		@param mixed $id The id of the request (Omit this for notifications!)
 */
class Tivoka_jsonRpcConnection
{

	public $target;
	
	public function __construct($server_addr)
	{
		$this->target = filter_var($server_addr, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED) ? $server_addr : '';
	}
	
	public function batch(array $batch)
	{
		//prepare requests...
		$ids = array();
		$requests = array();
		foreach($batch as $request)
		{
			if(isset($request['id'],$request['method']))//request...
			{
				if(in_array($request['id'],$ids,true))
					continue;
				$request['jsonrpc'] = '2.0';
				$requests[] = $request;
				$ids[] = $request['id'];
			}else
			if(!isset($request['id']) && isset($request['method']))//notification...
			{
				$request['jsonrpc'] = '2.0';
				$requests[] = $request;
			}
		}
		//pack them...
		$json = json_encode($requests);
		
		//send request
		$response = $this->_request($json);
		if($response->error()) return $response;
		$response = $response->response;
		
		//decode...
		$respassoc = json_decode($response,true);
		if($respassoc === NULL)
		{
			$resp = new Tivoka_jsonRpcResponse(FALSE);
			$resp->_processerror = 'Syntax Error: the received response could not be verified as valid JSON (JSON Error: '.$resp->json_errors[json_last_error()].')' .'Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
			return $resp;
		}
		
		//validate...
		if(count($respassoc) <= 1 || !is_array($respassoc))
		{
			$resp = new Tivoka_jsonRpcResponse(FALSE);
			$resp->_processerror = 'Error: Batch response expected. Single or empty response array received.' .'Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
			return $resp;
		}
		
		//split..
		foreach($respassoc as $resp)
		{
			if(!in_array($resp['id'],$ids,TRUE))//If the given id is not in the list...
			{
				if($resp['id'] == null)//notification...
				{
					$nullresps[] = $resp;
					continue;
				}
				continue;
			}
			//normal request...
			$responses[$resp['id']] = new Tivoka_jsonRpcResponse(json_encode($resp),$resp['id']);
			unset( $ids[	array_search($resp['id'],$ids, TRUE) ] );
		}
		
		//handle id:null responses...
		foreach($ids as $id)
		{
			$resp = array_shift($nullresps);
			$responses[$id] = new Tivoka_jsonRpcResponse( json_encode($resp), $id);
		}
		$resp = new Tivoka_jsonRpcResponse(FALSE);
		$resp->result = $responses;
		return $resp;
	}

	
	public function request($id,$method,$params='')
	{
		//prepare...
		$json = array(
			'jsonrpc'=>'2.0',
			'method'=>&$method,
			'id'=>&$id
			);
		if($params != '')$json['params'] = &$params;
		
		//send request...
		$json = json_encode($json);
		return $this->_request($json,$id);
	}

	public function notification($method,$params='')
	{
		//prepare...
		$json = array(
			'jsonrpc'=>'2.0',
			'method'=>&$method
		);
		if($params!='')$json['params'] = $params;
		
		//send request...
		return $this->_request(json_encode($json));
	}
	
	public function & _request(&$json,&$id='')
	{
		$url = parse_url($this->target);
		switch($url['scheme'])
		{
			case 'http':
				$resp['response'] = false;
				
				//preparing...
				$request = "GET ".$url['path']." HTTP/1.1\r\n"
					. "Host: ".$url['host']."\r\n"
					. "Content-Type: application/json\r\n"
					. "Content-Length: ".strlen($json)."\r\n"
					. "Connection: Close\r\n\r\n"
					. $json;
					
				//connecting...
				$socket = fsockopen($url['host'], 80, $errno, $errstr);
				if (!$socket)
				{
					$resp = new Tivoka_jsonRpcResponse(FALSE,$id);
					$resp->_processerror = 'Connect error (\'fsockopen\' failed): '.$errstr;
					return $resp;
				}
				
				//sending...
				if(fwrite($socket, $request,strlen($request)) === 0)
				{
					$resp = new Tivoka_jsonRpcResponse(FALSE,$id);
					$resp->_processerror = 'Connection error (\'fputs\' failed): Could not deliver data';
					return $resp;
				}
				
				//receiving response...
				stream_set_timeout ($socket, 10);
				$httpresp = stream_get_contents($socket);
				if($httpresp === FALSE)
				{
					$resp = new Tivoka_jsonRpcResponse(FALSE,$id);
					$resp->_processerror = 'Connection error (\'stream_get_contents\' failed): Ressource probably does not exist';
					return $resp;
				}
				
				if(strpos(substr($httpresp,0,50),'404 Not Found') !== FALSE)
				{
					$resp = new Tivoka_jsonRpcResponse(FALSE,$id);
					$resp->_processerror = 'HTTP error: Target not found (404)';
					return $resp;
				}
				
				$response = '';
				if(strpos($httpresp,"\r\n\r\n") !== FALSE)
				{
					list(,$response) = explode("\r\n\r\n",$httpresp,2);
				}
				break;
				
			default:
				$resp = new Tivoka_jsonRpcResponse(FALSE);
				$resp->_processerror = 'Connection error: No (valid) scheme given for server URL.';
				return $resp;
		}
		fclose($socket);
		$resp = new Tivoka_jsonRpcResponse($response,$id);
		return $resp;
	}

}

/*
 * Tivoka_jsonRpcResponse
 * Processes the response an acts as an interface for dealing with it
 *
 * @property string $response The original received response.
 * @property mixed $result The extracted, decoded and sanitized result.
 * @property array $error The extracted, decoded and sanitized error. Contains three items: 'msg' (Error message), 'code'(The defined error code), 'data'(More information about the error)
 * @property string $_processerror Contains the error message of an error that occured while connecting th server.
 * @method public __construct($response,$id='')
 *		@param string $param The original received response
 *		@param mixed $id The id of the associated request
 * @method public error() Returns a boolean TRUE if an error occured
 */
class Tivoka_jsonRpcResponse
{
	public $response;
	
	public $result;
	public $error;
	public $_processerror;
	
	public $json_errors;
	
	public function __construct($response,&$id='')
	{
		$this->result = NULL;
		$this->error = NULL;
		$this->_processerror = NULL;
		$this->response = $response;
		
		$this->json_errors = array(
		    JSON_ERROR_NONE => 'No error',
		    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		    JSON_ERROR_SYNTAX => 'Syntax error',
		);
		
		//analyze response...
		
		if($response === FALSE)//process error!
		{
			return;
		}
		
		if($id == '')//notification - no response required
		{
			return;
		}
		
		if(trim($response) == '')//no response!
		{
			$this->_processerror = 'Error: no response received';
			return;
		}
		
		//decode
		$respassoc = json_decode($response,true);
		
		if($respassoc == NULL)
		{
			$this->_processerror = 'Syntax Error: the received response could not be recognized as valid JSON (JSON: '.$this->json_errors[json_last_error()].')'.'Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
			return;
		}
		
		if(self::_is('error',$respassoc,$id))//server error!
		{
			$this->error['msg'] = $respassoc['error']['message'];
			$this->error['code'] = $respassoc['error']['code'];
			$this->error['data'] = $respassoc['error']['data'];
			return;
		}
		
		if(self::_is('result',$respassoc,$id))//valid result!
		{
			$this->result = $respassoc['result'];
			return;	
		}
		
		$this->_processerror = 'Error: The received response is invalid.'.'Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
		return;
	}
	
	public function error()
	{
		if($this->_processerror != NULL || $this->error != NULL)return TRUE;
		return FALSE;
	}
	
	public static function _is($type,$assoc,&$id)
	{
		switch($type)
		{
			case 'result':
				if(	$assoc['jsonrpc'] == '2.0'	&&
					isset($assoc['result'])	)
				{
					if($assoc['id'] == $id || !isset($assoc['id'])) return TRUE;
				}
				break;
			case 'error':
				if(	$assoc['jsonrpc'] == '2.0'	&&
					isset($assoc['error'])	)
				{
					if($assoc['id'] == $id || $assoc['id'] == null) return TRUE;
				}
				break;
		}
		return FALSE;		
	}
}
?>