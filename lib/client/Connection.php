<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * JSON-RPC client
 * @package Tivoka
 */
class Tivoka_Connection {

	public $target;
	
	/**
	 * Constructs connection
	 * @access private
	 * @param string $target URL
	 */
	public function __construct($target) {
		//validate url...
		if(!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
			throw new Tivoka_Exception('Valid URL (scheme://domain[/path][/file]) required.', Tivoka::ERR_INVALID_TARGET);
		
		//validate scheme...
		$t = parse_url($target);
		if($t['scheme'] != 'http' && $t['scheme'] != 'https')
			throw new Tivoka_Exception('Unknown or unsupported scheme given.', Tivoka::ERR_INVALID_TARGET);
		
		$this->target = $target;
	}
	
	/**
	 * Sends a JSON-RPC request
	 * @param Tivoka_Request $request A Tivoka request
	 * @return Tivoka_Request if sent as a batch request the BatchRequest object will be returned
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
					'method' => 'POST',
					'timeout' => 10.0
		)
		));
	
		//sending...
		$response = @file_get_contents($this->target, false, $context);
		if($response === FALSE) {
			throw new Tivoka_Exception('Connection to "'.$this->target.'" failed', Tivoka::ERR_CONNECTION_FAILED);
		}
		
		$request->setResponse($response);
		return $request;
	}
	
	/**
	 * Send a request directly
	 * @param mixed $id
	 * @param string $method
	 * @param array $params
	 */
	public function sendRequest($id, $method, $params=null) {
		$request = Tivoka::createRequest($id, $method, $params);
		$this->send($request);
		return $request;
	}
	
	/**
	 * Send a notification directly
	 * @param string $method
	 * @param array $params
	 */
	public function sendNotification($method, $params=null) {
		$this->send(Tivoka::createNotification($method, $params));
	}
}
?>
