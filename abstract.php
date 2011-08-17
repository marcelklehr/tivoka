<?php
/**
 *	Tivoka - a JSON-RPC implementation for PHP
 *	Copyright (C) 2011  Marcel Klehr
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
 * @author Marcel Klehr
 * @copyright (c) 2011, Marcel Klehr
 */
 /**
  * The source of all request objects
  * @package Tivoka
  */
abstract class Tivoka_ClientRequest
{
	/**
	 * @var mixed The id of the request (null if no)
	 */
	public $id;
	
	/**
	 * Should return the plain json encoded request
	 * @return string
	 */
	abstract public function getRequest();
	
	/**
	 * Converts the json string into structured data and returns them
	 *
	 * @param string $response The plain json data
	 * @return mixed Usually this method returns a Tivoka_ClientResponse object
	 */
	abstract public function processResponse($response);
	
	/**
	 * Gets called on a process error
	 *
	 * @param int $error A value of the Tivoka_ClientResponse::ERROR_* constants
	 * @return mixed Usually returns a Tivoka_ClientResponse object
	 */
	abstract public function processError($error);
}

class Tivoka_Exception extends Exception
{
	public function __construct($message = '', $code = 0, Exception $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}

class Tivoka_InvalidTargetException extends Tivoka_Exception
{
	public function __construct($message = '', $code = 0, Exception $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}
?>