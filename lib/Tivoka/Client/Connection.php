<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */

namespace Tivoka\Client;
use Tivoka\Exception;
use Tivoka\Tivoka;

/**
 * JSON-RPC connection
 * @package Tivoka
 */
class Connection {

	public $target;
	
	public $spec = Tivoka::SPEC_2_0;
	
	/**
	 * Constructs connection
	 * @access private
	 * @param string $target URL
	 */
	public function __construct($target) {
		//validate url...
		if(!filter_var($target, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED))
			throw new Exception\Exception('Valid URL (scheme://domain[/path][/file]) required.');
		
		//validate scheme...
		$t = parse_url($target);
		if(strtolower($t['scheme']) != 'http' && strtolower($t['scheme']) != 'https')
			throw new Exception\Exception('Unknown or unsupported scheme given.');
		
		$this->target = $target;
	}
	
	/**
	 * Sets the spec version to use for this connection
	 * @param string $spec The spec version (e.g.: "2.0")
	 */
	public function useSpec($spec) {
		$this->spec = Tivoka::validateSpecVersion($spec);
		return $this;
	}
	
	
	/**
	 * Sends a JSON-RPC request
	 * @param Tivoka_Request $request A Tivoka request
	 * @return Tivoka_Request if sent as a batch request the BatchRequest object will be returned
	 */
	public function send($request) {
		if(func_num_args() > 1 ) $request = func_get_args();
		if(is_array($request)) {
			$request = new BatchRequest($request);
		}
		
		if(!($request instanceof Request)) throw new Exception\Exception('Invalid data type to be sent to server');
		
		// preparing connection...
		$context = stream_context_create(array(
				'http' => array(
					'content' => $request->getRequest($this->spec),
					'header' => "Content-Type: application/json\r\n".
								"Connection: Close\r\n",
					'method' => 'POST',
					'timeout' => 10.0
		)
		));
	
		//sending...
		$response = @file_get_contents($this->target, false, $context);
		if($response === FALSE) {
			throw new Exception\ConnectionException('Connection to "'.$this->target.'" failed');
		}
		
		$request->setResponse($response);
		return $request;
	}
	
	/**
	 * Send a request directly
	 * @param string $method
	 * @param array $params
	 */
	public function sendRequest($method, $params=null) {
		$request = new Request($method, $params);
		$this->send($request);
		return $request;
	}
	
	/**
	 * Send a notification directly
	 * @param string $method
	 * @param array $params
	 */
	public function sendNotification($method, $params=null) {
		$this->send(new Notification($method, $params));
	}
	
	/**
	 * Creates a native remote interface for the target server
	 * @return Tivoka\Client\NativeInterface
	 */
	public function getNativeInterface()
	{
		return new NativeInterface($this);
	}
}
?>
