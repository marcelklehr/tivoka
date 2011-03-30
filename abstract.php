<?php
abstract Tivoka_Request
{
	public $id;
	abstract public function getRequest();
	abstract public function setResponse($response);
}
?>