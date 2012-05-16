<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * JSON-RPC native remote interface
 * @package Tivoka
 */
class Tivoka_NativeInterface {
	
	/**
	 * Holds the last request
	 * @var integer
	 */
	public $last_request;
	
	/**
	 * Holds the connection to the remote server
	 * @var Tivoka_Connection
	 */
	public $connection;
	
	/**
	 * Construct a native remote interface
	 * @param Tivoka_Connection $target URL
	 */
	public function __construct(Tivoka_Connection $connection) {
		$this->connection = $connection;
	}
	
	/**
	 * Sends a JSON-RPC request
	 * @param Tivoka_Request $request A Tivoka request
	 * @return void
	 */
	public function __call($method, $args) {
		$this->last_request = Tivoka::createRequest($method, $args);
		$this->connection->send($this->last_request);
		
		if($this->last_request->isError()) {
			throw new Tivoka_Exception($this->last_request->errorMessage, $this->last_request->error);
		}
		return $this->last_request->result;
	}

}
?>