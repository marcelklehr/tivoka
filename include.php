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

require_once(dirname(__FILE__).'/abstract.php');
require_once(dirname(__FILE__).'/client_connection.class.php');
require_once(dirname(__FILE__).'/client_response.class.php');
require_once(dirname(__FILE__).'/client_request.class.php');
require_once(dirname(__FILE__).'/client_notification.class.php');
require_once(dirname(__FILE__).'/client_batch.class.php');
require_once(dirname(__FILE__).'/server_server.class.php');
require_once(dirname(__FILE__).'/server_processor.class.php');
require_once(dirname(__FILE__).'/server_arrayhost.class.php');
?>