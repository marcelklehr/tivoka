<?php
/*
 * Tivoka_Server
 * Provides the methods of the given host object for invokation through the JSON-RPC protocol
 *
 * Notice: Instanciating this class will stop further script execution, so place this command at the end of your script!
 *
 * @method public __construct($host,$errors=FALSE)
 *		@param object $host An object whose methods will be provided for invokation
 *		@param bool $errors Display Errors (Optional)
 * @method private _is($type,$assoc)
 *		@param string $type The request type to detect
 *		@param array $assoc The decoded JSON-RPC request to examine
 * @method public result($id,$result)
 *		@param mixed $id The id of the associated request
 *		@param mixed $result The result of the request given by $id
 * @method public error($id,$code,$data)
 *		@param mixed $id The id of the associated request
 *		@param integer $code The code of the error which occured processing the request given by $id
 *		@param mixed $data The more information about the error
 */
class Tivoka_Server
{
	public $host;
	public $input;
	public $response;

	public function __construct($host, $errors=FALSE)
	{
		//define some things...
		if($errors != FALSE)error_reporting(0);//avoids messing up the response
		$this->host = &$host;
		$this->input = FALSE;
		$input = file_get_contents('php://input');
		$json_errors = array(
		    JSON_ERROR_NONE => '',
		    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		    JSON_ERROR_SYNTAX => 'Syntax error'
		);
	}
	
	public function process()
	{
		//set header if not already sent...
		if(!headers_sent()) header('Content-type: application/json');
		
		//validate input...
		
		//check existence...
		if(trim($input) == '')
		{
			$this->error(null,-32600);
			$this->respond();
		}
		
		//decode request...
		$this->input = json_decode($input,true);
		if($this->input === NULL)
		{
			$this->error(null,-32700, 'JSON: '.$json_errors[json_last_error()] );
			$this->respond();
		}
		
		//process batch...
		if(self::_is('batch',$this->input))
		{
			foreach($this->input as $request)
			{
				new Tivoka_Processor($request,$this);
			}
			$this->respond();
		}
		
		//process request
		new Tivoka_Processor($this->input,$this);
		$this->respond();
	}
	
	protected function respond()
	{
		if(!is_array($this->response))//no array
		{
			exit;
		}
		$count = count($this->response);
		if($count == 1)//single request
		{
			print json_encode($this->response[0]);
			exit;
		}
		if($count > 1)//batch request
		{
			print json_encode($this->response);
			exit;
		}
		if($count < 1)//no response
		{
			exit;
		}
	}
	
	private static function _is($type,$assoc)
	{
		switch($type)
		{
			case 'request':
				if(	isset($assoc['jsonrpc'])	&&
					isset($assoc['id'])		&&
					isset($assoc['method'])
				)
				{
				   if($assoc['jsonrpc'] == '2.0')return TRUE;
				}
				   break;
			case 'notification':
				if(	isset($assoc['jsonrpc'])	&&
					!isset($assoc['id'])	&&
					isset($assoc['method'])
				)
				{
				   if($assoc['jsonrpc'] == '2.0')return TRUE;
				}
				break;
			case 'batch':
				if(	is_array($assoc) &&
					count($assoc) > 1 &&
					!self::_is('request',$assoc) &&
					!self::_is('notification',$assoc)
				)
				return TRUE;
				break;
		}
		return FALSE;		
	}
	
	//callbacks
	
	public function returnResult(&$id,&$result)
	{
		$this->response[] = array(
				'jsonrpc'=>'2.0',
				'id'=>&$id,
				'result'=>&$result
		);
	}
	
	public function returnError($id,$code,$data='')
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
				'id'=>&$id,
				'error'=> array(
					'code'=>&$code,
					'message'=>&$msg[$code],
					'data'=>&$data
		));
	}
}
?>