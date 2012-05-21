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

include $dir.'/Client/Connection.php';
include $dir.'/Client/Request.php';
include $dir.'/Client/Notification.php';
include $dir.'/Client/BatchRequest.php';
include $dir.'/Client/NativeInterface.php';

include $dir.'/Server/Server.php';
include $dir.'/Server/Processor.php';
include $dir.'/Server/MethodWrapper.php';
?>