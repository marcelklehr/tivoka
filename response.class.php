<?
/*
 * Tivoka_jsonRpcResponse
 * Processes the response an acts as an interface for dealing with it
 *
 * @property string $response The original received response.
 * @property mixed $result The extracted, decoded and sanitized result.
 * @property array $error The extracted, decoded and sanitized error. Contains three items: 'msg' (Error message), 'code'(The defined error code), 'data'(More information about the error)
 * @property string $_processerror Contains the error message of an error that occured while connecting th server.
 * @method public __construct($response,$id='')
 *		@param string $param The original received response
 *		@param mixed $id The id of the associated request
 * @method public error() Returns a boolean TRUE if an error occured
 */
class Tivoka_jsonRpcResponse
{
	public $response;
	
	public $result;
	public $error;
	public $_processerror;
	
	public $json_errors;
	
	public function __construct($response,&$id='')
	{
		$this->result = NULL;
		$this->error = NULL;
		$this->_processerror = NULL;
		$this->response = $response;
		
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
			$this->_processerror = 'Syntax Error: the received response could not be recognized as valid JSON (JSON: '.$this->json_errors[json_last_error()].')'.'Response:<br/><pre>'.htmlspecialchars($response).'</pre>';
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
	
	public function error()
	{
		if($this->_processerror != NULL || $this->error != NULL)return TRUE;
		return FALSE;
	}
	
	public static function _is($type,$assoc,&$id)
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