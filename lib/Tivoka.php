<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <mklehr@gmx.net>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * The public interface to all tivoka functions
 * @package Tivoka
 */
abstract class Tivoka
{
	const ERR_NO_RESPONSE = 1;      // 000 000 001
	const ERR_INVALID_JSON = 2;     // 000 000 010
	const ERR_INVALID_RESPONSE = 3; // 000 000 011
	const ERR_CONNECTION_FAILED = 4;// 000 000 100
	const ERR_SPEC_INCOMPATIBLE = 5;// 000 000 101
	
	const ERR_INVALID_TARGET = 6;   // 000 000 110
	const HIDE_ERRORS = 7;          // 000 000 111
	
	const SPEC_1_0 = 8;             // 000 001 000
	const SPEC_2_0 = 16;            // 000 010 000
	
	
	/**
	 * Initializes a Connection to a remote server
	 * @param string $target the URL of the target server
	 * @return Tivoka_Connection
	 */
	public static function connect($target) {
		return new Tivoka_Connection($target);
	}
	
	/**
	 * Creates a request
	 * @throws Tivoka_Exception
	 * @param mixed $id The id of the request
	 * @param string $method The method to invoke
	 * @param array $params The parameters
	 * @return Tivoka_Request
	 */
	public static function createRequest($method, $params=null) {
		return new Tivoka_Request($method, $params);
	}
	
	/**
	 * Creates a notification
	 * @throws Tivoka_Exception
	 * @param string $method The method to invoke
	 * @param array $params The parameters
	 */
	public static function createNotification($method, $params=null) {
		return new Tivoka_Notification($method, $params);
	}
	
	/**
	 * Creates a batch request
	 * @param mixed $request either an array of requests or a comma-seperated list of requests
	 * @throws Tivoka_Exception
	 * @return Tivoka_BatchRequest
	 */
	public static function createBatch($request) {
		if(func_num_args() > 1 ) $request = func_get_args();
		if(is_array($request)) {
			return new Tivoka_BatchRequest($request);
		}
		throw new Tivoka_Exception('Object of invalid data type passed to Tivoka::createBatch.');
	}
	
	/**
	 * Starts processing the HTTP input
	 * Notice: Calling this method will stop further execution of the script!
	 * @param object $host An object whose methods will be provided for invokation
	 * @param integer $hide_errors Optionally pass `Tivoka::HIDE_ERRORS` to hide all errors from the output
	 * @return Tivoka_Server
	 */
	static function createServer($host, $hide_errors=0)
	{
		return new Tivoka_Server($host);
	}
	
	/**
	 * Evaluates and returns the passed JSON-RPC spec version
	 * @private
	 * @param string $version spec version as a string (using semver notation)
	 */
	static function useSpec($version)
	{
		switch($version) {
			case '1.0':
				return Tivoka::SPEC_1_0;
				break;
			case '2.0':
				return Tivoka::SPEC_2_0;
			default:
				throw new Tivoka_Exception('Unsupported spec version: '+version);
		}
	}
	
	/**
	 * Returns a v4 uuid
	 */
	static function uuid()
	{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), // time_low
			mt_rand(0, 0xffff), // time_mid
			mt_rand(0, 0x0fff) | 0x4000, // time_hi_and_version
			mt_rand(0, 0x3fff) | 0x8000, // clk_seq_hi_res/clk_seq_low
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff) // node
		);
	}
}
?>