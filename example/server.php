<?php
include('../include.php');

$methods = array(
	'demo.sayHello' => function() {
		return 'Hello World!';
	},
	
	'demo.substract' => function($params) {
		list($num1, $num2) = $params;
		return $num1 - $num2;
	}
);

Tivoka\Server::provide($methods)->dispatch();
?>