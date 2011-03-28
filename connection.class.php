<?php
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
	public $connection;
	public $target;
	
	public function __construct($server_addr)
	{
		//validate url...
		if(!filter_var($server_addr, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)){ throw new InvalidArgumentException('Valid URL (scheme,domain[,path][,file]) required.'); return; }
		$this->target = parse_url($server_addr);
		
		if($this->target['scheme'] !== 'http'){ throw new InvalidArgumentException('Unknown or unsupported scheme given: \''.htmlspecialchars($this->target['url']).'\''); return; }
		
		//connecting...
		$this->connection = fsockopen($this->target['host'], 80, $errno, $errstr);
		if(!$this->connection)	throw new InvalidArgumentException('Cannot connect to the given URL (\'fsockopen\' failed)');
		
	}
	
	public function __destruct()
	{
		fclose($this->connection);
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
			$resp->_processerror = 'Syntax Error: The received response could not be verified as valid JSON (JSON Error: '.$resp->json_errors[json_last_error()].')' .' Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
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
		//preparing...
		$request = "GET ".$this->target['path']." HTTP/1.1\r\n"
			. "Host: ".$this->target['host']."\r\n"
			. "Content-Type: application/json\r\n"
			. "Content-Length: ".strlen($json)."\r\n"
			. "Connection: Close\r\n\r\n"
			. $json;
		
		//sending...
		if(fwrite($this->connection, $request, strlen($request)) === 0)
		{
			$resp = new Tivoka_jsonRpcResponse(FALSE,$id);
			$resp->_processerror = 'Connection error (\'fputs\' failed): Could not deliver data';
			return $resp;
		}
		
		//receiving response...
		stream_set_timeout ($this->connection, 10);
		$httpresp = stream_get_contents($this->connection);
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
		
		$resp = new Tivoka_jsonRpcResponse($response,$id);
		return $resp;
	}

}
?>