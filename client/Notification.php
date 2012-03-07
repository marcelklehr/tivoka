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
* JSON-RPC notification
* @package Tivoka
*/
class Tivoka_Notification extends Tivoka_Request
{
	/**
	* Constructs a new JSON-RPC notification object
	* @param string $method The remote procedure to invoke
	* @param mixed $params Additional params for the remote procedure
	* @see Tivoka_Connection::send()
	*/
	public function __construct($method,$params=null)
	{
		$this->id = null;
		$this->response = new Tivoka_Response($this);
	
		//prepare...
		$this->data = self::prepareRequest(null, $method, $params);
	}
}
?>