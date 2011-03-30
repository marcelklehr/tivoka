<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <marcel.klehr@gmx.de>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * Opens a connection to the given JSONJ-RPC server for invoking the provided remote procedures
 *
 * @package Tivoka
 */
class Tivoka_Connection
{
	/**
	 * @var ressource The ressource returned by fsockopen()
	 */
	public $connection;
	
	/**
	 * @var array The target, parsed by parse_url()
	 */
	public $target;
	
	/**
	 * Initializes a Tivoka_Connection object
	 *
	 * @param string $target the URL of the target server (MUST include http scheme)
	 */
	public function __construct($target)
	{
		//validate url...
		if(!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
			{ throw new InvalidArgumentException('Valid URL (scheme,domain[,path][,file]) required.'); return; }
		$this->target = parse_url($target);
		
		if($this->target['scheme'] !== 'http')
			{ throw new InvalidArgumentException('Unknown or unsupported scheme given: \''.htmlspecialchars($this->target['url']).'\''); return; }
		
		//connecting...
		$this->connection = fsockopen($this->target['host'], 80, $errno, $errstr);
		if(!$this->connection)	throw new InvalidArgumentException('Cannot connect to the given URL (\'fsockopen\' failed)');
	}
	
	public function __destruct()
	{
		fclose($this->connection);
	}
	
	/**
	 * Sends a JSON-RPC request to the defined target
	 *
	 * @param array $batch A list of request arrays, each containing 'method', 'params' (optional) and 'id' (optional)
	 * @see Tivoka_Response
	 * @return Tivoka_Response
	 */
	public function send(Tivoka_Request $request)
	{
	
	}
	
	function sendBatch() {
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
		if($response->isError()) return $response;
		$response = $response->response;
		
		//decode...
		$respassoc = json_decode($response,true);
		if($respassoc === NULL)
		{
			$resp = new Tivoka_Response(FALSE);
			$resp->_processerror = 'Syntax Error: The received response could not be verified as valid JSON (JSON Error: '.$resp->json_errors[json_last_error()].')' .' Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
			return $resp;
		}
		
		//validate...
		if(count($respassoc) <= 1 || !is_array($respassoc))
		{
			$resp = new Tivoka_Response(FALSE);
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
			$responses[$resp['id']] = new Tivoka_Response(json_encode($resp),$resp['id']);
			unset( $ids[	array_search($resp['id'],$ids, TRUE) ] );
		}
		
		//handle id:null responses...
		foreach($ids as $id)
		{
			$resp = array_shift($nullresps);
			$responses[$id] = new Tivoka_Response( json_encode($resp), $id);
		}
		$resp = new Tivoka_Response(FALSE);
		$resp->result = $responses;
		return $resp;
	}

	/**
	 * Sends a JSON-RPC request to the defined target
	 *
	 * @param mixed $id A unique request identifier
	 * @param string $method The method to invoke
	 * @param mixed $params The parameters for the method to invoke
	 * @see Tivoka_Response
	 * @return Tivoka_Response
	 */
	public function sendRequest($id,$method,$params='')
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
	
	/**
	 * Sends a JSON-RPC notification to the defined target
	 *
	 * @param string $method The method to invoke
	 * @param mixed $params The parameters for the method to invoke
	 * @see Tivoka_Response
	 * @return Tivoka_Response
	 */
	public function sendNotification($method,$params='')
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
	
	/**
	 * Sends the JSON data to the defined target
	 *
	 * @param string $json The json_encoded request
	 * @param mixed $id The request id (optional for notifications)
	 * @see Tivoka_Response
	 * @return Tivoka_Response
	 */
	private function & _request($json,&$id='')
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
			$resp = new Tivoka_Response(FALSE,$id);
			$resp->_processerror = 'Connection error (\'fputs\' failed): Could not deliver data';
			return $resp;
		}
		
		//receiving response...
		stream_set_timeout ($this->connection, 10);
		$httpresp = stream_get_contents($this->connection);
		if($httpresp === FALSE)
		{
			$resp = new Tivoka_Response(FALSE,$id);
			$resp->_processerror = 'Connection error (\'stream_get_contents\' failed): Ressource probably does not exist';
			return $resp;
		}
		
		if(strpos(substr($httpresp,0,50),'404 Not Found') !== FALSE)
		{
			$resp = new Tivoka_Response(FALSE,$id);
			$resp->_processerror = 'HTTP error: Target not found (404)';
			return $resp;
		}
		
		$response = '';
		if(strpos($httpresp,"\r\n\r\n") !== FALSE)
		{
			list(,$response) = explode("\r\n\r\n",$httpresp,2);
		}
		
		$resp = new Tivoka_Response($response,$id);
		return $resp;
	}
}
?>