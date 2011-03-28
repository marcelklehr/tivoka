<?php
/*
 *	Tivoka - a JSON-RPC implementation for PHP
 *	Copyright (C) 2011  Marcel Klehr (marcel.klehr@gmx.de)
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
 */

/*
 * Tivoka_jsonRpcArrayHost
 * Helper class for registering anonymous function at the server on the fly
 *
 * @method public __construct($methods)
 *		@param array $methods An array with the names of the methods as keys and anonymous functions as their values
 * @method public register($name,$method)
 *		@param string $name The name of the method to register
 *		@param function $method An anonymous function to execute
 * @method public exist($name) 
 *		@param string $name The name of the method to check for existence
 */
class Tivoka_jsonRpcArrayHost
{
	protected $methods;
	
	public function __construct(array $methods)
	{
		foreach($methods as $name=>$method)
		{
			$this->register($name,$method);
		}
	}
	
	public function register($name,$method)
	{
		if(!is_callable($method)){ throw new BadFunctionCallException('Valid Callback reqired, uncallable function given for \''.htmlspecialchars($name).'\''); return FALSE;}
		
		$this->methods[$name] = $method;
		return TRUE;
	}
	
	public function exist($method)
	{
		if(!is_array($this->methods))return FALSE;
		if(is_callable($this->methods[$method]))return TRUE;
	}
	
	public function __call($method,$args)
	{
		if(!$this->exist($method)){$args[0]->error(-32601); return;}
		$prc = $args[0];
		call_user_func_array($this->methods[$method],array($prc));
	}
}


/*
 * Tivoka_jsonRpcServer
 * Provides the methods of the given host object for invokation through the JSON-RPC protocol
 *
 * Notice: Instanciating this class will stop further script execution, so place this command at the end of your script!
 *
 * @method public __construct($host,$errors=FALSE)
 *		@param object $host An object whose methods will be provided for invokation
 *		@param bool $errors Display Errors (Optional)
 * @method public _is($type,$assoc)
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
class Tivoka_jsonRpcServer
{
	public $host;
	public $input;
	public $response;

	public function __construct($host,$errors=FALSE)
	{
		//define some things...
		if((bool)$errors)error_reporting(0);//avoids messing up the response
		$this->host = &$host;
		$this->input = FALSE;
		$input = file_get_contents('php://input');
		$json_errors = array(
		    JSON_ERROR_NONE => '',
		    JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
		    JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
		    JSON_ERROR_SYNTAX => 'Syntax error'
		);
		
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
		if($this->_is('batch',$this->input))
		{
			foreach($this->input as $request)
			{
				new jsonRPC_processor($request,$this);
			}
			$this->respond();
		}
		
		//process request
		new jsonRPC_processor($this->input,$this);
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
	
	public function _is($type,$assoc)
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
					!$this->_is('request',$assoc) &&
					!$this->_is('notification',$assoc)
				)
				return TRUE;
				break;
		}
		return FALSE;		
	}
	
	//callbacks
	
	public function result(&$id,&$result)
	{
		$this->response[] = array(
				'jsonrpc'=>'2.0',
				'id'=>&$id,
				'result'=>&$result
		);
	}
	
	public function error($id,$code,$data='')
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
/*
 * jsonRPC_processor
 * Validates the request and interacts between the server and the called method
 *
 * @method public __construct($request,&$server)
 *		@param string $request The plain json encoded request
 *		@param Tivoka_jsonRpcServer $server Reference to the server for returning the result/error
 * @method public result($result)
 *		@param mixed $result The result of the request given with __construct
 * @method public error($code,$data='')
 *		@param integer $code The code of the error which occured processing the request given with __construct
 *		@param mixed $data The more information about the error
 */
class jsonRPC_processor
{
	protected $server;
	protected $request;
	public $params;
	
	public function __construct($request,Tivoka_jsonRpcServer &$server)
	{
		$this->server = &$server;
		$this->request = &$request;
		$this->params = (isset($this->request['params'])) ? $this->request['params'] : null;
		
		//validate...
		if(!$this->server->_is('request',$this->request) && !$this->server->_is('notification',$this->request))
		{
			$this->error(-32600);
			return;
		}
		
		//search method...
		if(!is_callable(array($this->server->host,$this->request['method'])))
		{
			$this->error(-32601);
			return;
		}
		
		//invoke...
		$this->server->host->{$this->request['method']}($this);
	}
	
	//callbacks...
	
	public function error($code,$data=null)
	{
		if(!$this->server->_is('request',$this->request)) return;
		
		$id = (!isset($this->request['id'])) ? null : $this->request['id'];
		$this->server->error($id,$code,$data);
	}
	
	public function result($result)
	{
		if(!$this->server->_is('request',$this->request)) return;
		$this->server->result($this->request['id'],$result);
	}
}
?>