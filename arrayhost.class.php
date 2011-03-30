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
 /**
 * @package Tivoka
 * @author Marcel Klehr <marcel.klehr@gmx.de>
 * @copyright (c) 2011, Marcel Klehr
 */
/**
 * Helper class for registering server methods on the fly
 *
 * @package Tivoka
 */
class Tivoka_ArrayHost
{
	/**
	 * @var array The list of callbacks
	 */
	private $methods;
	
	/**
	 * Initializes a Tivoka_ArrayHost object
	 *
	 * @param array $methods A list of valid callbacks with the name of the server method as keys
	 */
	public function __construct(array $methods)
	{
		foreach($methods as $name=>$method)
		{
			$this->register($name,$method);
		}
	}
	
	/**
	 * Registers a server method
	 *
	 * @param string $name The name of the method to provide (already existing methods with the same name will be overridden)
	 * @param callback $method The callback
	 * @returns bool
	 */
	public function register($name,$method)
	{
		if(!is_callable($method)){ throw new BadFunctionCallException('Valid Callback reqired, uncallable function given for \''.htmlspecialchars($name).'\''); return FALSE;}
		
		$this->methods[$name] = $method;
		return TRUE;
	}
	
	/**
	 * Returns TRUE if the method with the given name is registered and a valid callback
	 *
	 * @param callback $method The name of the method to check
	 * @returns bool
	 */
	public function exist($method)
	{
		if(!is_array($this->methods))return FALSE;
		if(is_callable($this->methods[$method]))return TRUE;
	}
	
	/**
	 * Invokes the requested method
	 */
	public function __call($method,$args)
	{
		if(!$this->exist($method)){$args[0]->error(-32601); return;}
		$prc = $args[0];
		call_user_func_array($this->methods[$method],array($prc));
	}
}
?>