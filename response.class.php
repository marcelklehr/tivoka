<?php
/**
 * @package Tivoka
 * @author Marcel Klehr <marcel.klehr@gmx.de>
 * @copyright (c) 2011, Marcel Klehr
 */
/*
 * Processes the response an acts as an interface for dealing with it
 *
 * @package Tivoka
 */
class Tivoka_Response
{
	/**
	 * @var mixed The received response in various forms, normally as an array
	 * @access private
	 */
	public $response;
	
	/**
	 * @var mixed The result as received from the target (NULL if an error occured)
	 */
	public $result;
	
	/**
	 * @var array The error as received from the target: $error["msg"] => the Error message, $error["code"] => the JSON-RPC error code, $error["data"] => Additional information (NULL if no error occured)
	 */
	public $error;
	
	/**
	 * @var string Contains information about an occured error while sending/processing the request (NULL if no process error occured)
	 */
	public $process_error; 
	
	/**
	 * @var array Contains error messages for json_decode error codes
	 * @access private
	 */
	public $json_errors;
	
	/**
	 * Initializes a Tivoka_Response object
	 *
	 * @param string $response The plain JSON-RPC response as received from the target
	 * @param mixed $id The id of the originally sent request
	 * @access private
	 */
	public function __construct($response,&$id='')
	{
		$this->result = NULL;
		$this->error = NULL;
		$this->_processerror = NULL;
		$this->response = &$response;
		
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
			$this->_processerror = 'Syntax Error: the received response could not be verified as valid JSON (JSON: '.$this->json_errors[json_last_error()].')'.'Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
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
	
	/**
	 * Determines whether an error occured
	 *
	 * @return bool
	 */
	public function isError()
	{
		if($this->process_error != NULL || $this->error != NULL)return TRUE;
		return FALSE;
	}
	
	/**
	 * Determines whether the response is an error or a result
	 *
	 * @param string $type Either 'result' or 'error'
	 * @param array $assoc The parsed JSON-RPC response
	 * @param mixed $id The id of the originally sent request
	 * @static
	 * @return bool
	 * @access private
	 */
	private static function _is($type,$assoc,&$id)
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