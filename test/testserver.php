<?php

require_once "Socket/Unix.php";

$socket = new Socket_Unix( array( 'local' => '/tmp/foo', 'listen' => 1 ) );

$t = $socket->accept();
print( $t->read( 1024 , PHP_NORMAL_READ ) );
print ($t->say( "world!" ) );
$t->shutdown();
$t->close();

$socket->shutdown();
$socket->close();
