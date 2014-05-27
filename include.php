<?php

$dir = dirname(__FILE__).'/lib/Tivoka';

include $dir.'/Tivoka.php';
include $dir.'/Client.php';
include $dir.'/Server.php';

include $dir.'/Exception/Exception.php';
include $dir.'/Exception/ConnectionException.php';
include $dir.'/Exception/RemoteProcedureException.php';
include $dir.'/Exception/SpecException.php';
include $dir.'/Exception/SyntaxException.php';
include $dir.'/Exception/ProcedureException.php';
include $dir.'/Exception/InvalidParamsException.php';

include $dir.'/Client/Connection/ConnectionInterface.php';
include $dir.'/Client/Connection/AbstractConnection.php';
include $dir.'/Client/Connection/Http.php';
include $dir.'/Client/Connection/Tcp.php';
include $dir.'/Client/Connection/WebSocket.php';
include $dir.'/Client/Request.php';
include $dir.'/Client/Notification.php';
include $dir.'/Client/BatchRequest.php';
include $dir.'/Client/NativeInterface.php';

include $dir.'/Server/Server.php';
include $dir.'/Server/MethodWrapper.php';
?>