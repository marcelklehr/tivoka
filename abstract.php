<?php
abstract class Tivoka_ClientRequest
{
	public $id;
	abstract public function getRequest();
	abstract public function getResponse($response);
	abstract public function processError($error);
	protected static function _isResult($assoc,$id)
	{
		if(	$assoc['jsonrpc'] == '2.0'	&&
			isset($assoc['result'])	)
		{
			if($assoc['id'] == $id || !isset($assoc['id'])) return TRUE;
		}
		return FALSE;	
	}
	protected static function _isError($assoc,$id)
	{
		if(	$assoc['jsonrpc'] == '2.0'	&&
			isset($assoc['error'])	)
		{
			if($assoc['id'] == $id || $assoc['id'] == null) return TRUE;
		}
		return FALSE;		
	}
}
?>