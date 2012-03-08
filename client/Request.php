<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * A JSON-RPC request
 * @package Tivoka
 */
class Tivoka_Request
{
	public $id;
	public $data;
	public $response;
	
	/**
	 * Constructs a new JSON-RPC request object
	 * @param mixed $id The id of the request
	 * @param string $method The remote procedure to invoke
	 * @param mixed $params Additional params for the remote procedure (optional)
	 * @see Tivoka_Connection::send()
	 */
	public function __construct($id,$method,$params=null) {
		$this->id = $id;
		$this->response = new Tivoka_Response($this);
	
		//prepare...
		$this->data = self::prepareRequest($id, $method, $params);
	}
	
	/**
	 * Send this request to a remote server directly
	 * @param string $target The URL of the remote server
	 */
	public function send($target) {
		Tivoka::connect($target)->send($this);
	}
	
	/**
	 * Pack the request data with json encoding
	 * @return string the json encoded request
	 */
	public function __toString() {
		return json_encode($this->data);
	}
	
	/**
	 * Encodes the request properties
	 * @param mixed $id The id of the request
	 * @param string $method The method to be called
	 * @param array $params Additional parameters
	 * @return mixed the prepared assotiative array to encode
	 */
	protected static function prepareRequest($id, $method, $params=null) {
		switch(Tivoka::$version) {
		case Tivoka::VER_2_0:
			$request = array(
					'jsonrpc' => '2.0',
					'method' => $method,
			);
			if($id !== null) $request['id'] = $id;
			if($params !== null) $request['params'] = $params;
			return $request;
		case Tivoka::VER_1_0:
			$request = array(
				'method' => $method,
				'id' => $id
			);
			if($params !== null) {
				if((bool)count(array_filter(array_keys($params), 'is_string'))) throw new Tivoka_Exception('JSON-RC 1.0 doesn\'t allow for named parameters');
				$request['params'] = $params;
			}
			return $request;
		}
	}
}
?>