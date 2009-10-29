<?php
require_once "Socket/Unix.php";

$socket = new Socket_Unix( '/tmp/foo' );

echo( $socket->say( "hello" ) );
echo("\n");
echo( $socket->read( 1024 , PHP_NORMAL_READ ) );
echo("\n");

$socket->shutdown();
$socket->close();
