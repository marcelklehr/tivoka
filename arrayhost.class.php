<?php
/*
 * Tivoka_ArrayHost
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
class Tivoka_ArrayHost
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
?>