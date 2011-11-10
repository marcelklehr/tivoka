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
* Processes the  JSON-RPC input
* @package Tivoka
*/
class Tivoka_Server
{
	/**
	* @var object The object given to __construct()
	* @see Tivoka_ServerServer::__construct()
	* @access private
	*/
	public $host;
	
	/**
	 * @var array The parsed json input as an associative array
	 * @access private
	 */
	private $input;
	
	/**
	 * @var array A list of associative response arrays to json_encode
	 * @access private
	 */
	private $response;
	
	/**
	 * Starts processing the HTTP input
	 * Notice: Calling this method will stop further execution of the script!
	 * @param object $host An object whose methods will be provided for invokation
	 * @param bool $hide_errors Pass TRUE for hiding all eventual php erros to avoid messing up the response
	 */
	static function start($host, $hide_errors=FALSE)
	{
		if($hide_errors != FALSE) error_reporting(0);//avoids messing up the response
		$json_errors = array(
			JSON_ERROR_NONE => '',
			JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
			JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
			JSON_ERROR_SYNTAX => 'Syntax error'
		);
		$server = new Tivoka_Server($host);
		
		//set header if not already sent...
		if(headers_sent() === FALSE) header('Content-type: application/json');
		
		//validate input...
		
		//check existence...
		if(trim($server->input) === '')
		{
			$server->returnError(null,-32600);
			$server->respond();
		}
		
		//decode request...
		$server->input = json_decode($server->input,true);
		if($server->input === NULL)
		{
			$server->returnError(null,-32700, 'JSON error: '.$json_errors[json_last_error()] );
			$server->respond();
		}
		
		//batch?
		if(($batch = self::interpretBatch($server->input)) !== FALSE)
		{
			foreach($batch as $request)
			{
				new Tivoka_Processor($request,$server);
			}
			$server->respond();
		}
		
		//process request
		new Tivoka_Processor($server->input,$server);
		$server->respond();
	}
	
	/**
	 * Constructss a Server object
	 * @param object $host An object whose methods will be provided for invokation
	 * @param bool $hide_errors Pass TRUE for hiding all eventual erros to avoid messing up the response
	 * @access private
	 */
	private function __construct($host)
	{
		//define some things...
		$this->host = $host;
		$this->input = file_get_contents('php://input');
	}
	
	/**
	* Receives the computed result
	*
	* @param mixed $id The id of the original request
	* @param mixed $result The computed result
	* @access private
	*/
	public function returnResult($id,$result)
	{
		$this->response[] = array(
					'jsonrpc' => '2.0',
					'id' => $id,
					'result' => $result
		);
	}
	
	/**
	 * Receives the error from computing the result
	 *
	 * @param mixed $id The id of the original request
	 * @param int $code The specified JSON-RPC error code
	 * @param mixed $data Additional data
	 * @access private
	 */
	public function returnError($id,$code,$data=null)
	{
		$msg = array(
			-32700 => 'Parse error',
			-32600 => 'Invalid Request',
			-32601 => 'Method not found',
			-32602 => 'Invalid params',
			-32603 => 'Internal error'
		);
		$this->response[] = array(
					'jsonrpc'=>'2.0',
					'id'=>$id,
					'error'=> array(
						'code'=>$code,
						'message'=>$msg[$code],
						'data'=>$data
		));
	}
	
	/**
	* Outputs the processed response
	* @access private
	*/
	public function respond()
	{
		if(!is_array($this->response))//no array
			exit;
		
		$count = count($this->response);
		
		if($count == 1)//single request
			die(json_encode($this->response[0]));
	
		if($count > 1)//batch request
			die(json_encode($this->response));
	
		if($count < 1)//no response
			exit;
	}
	
	/**
	* Validates a batch request
	*
	* @param array $assoc The json-parsed JSON-RPC request
	* @static
	* @return array Returns the original request and if it was invalid, a boolean FALSE is returned
	* @access private
	*/
	public static function interpretBatch(array $assoc)
	{
		if($count = count($assoc) <= 1)
		return FALSE;
	
		foreach($assoc as $req)
		{
			if(!is_array($req))
				return FALSE;
		}
		return $assoc;
	}
}
?>