#!/usr/bin/perl 
#===============================================================================
#
#         FILE:  socktest.pl
#
#        USAGE:  ./socktest.pl 
#
#  DESCRIPTION:  
#
#      OPTIONS:  ---
# REQUIREMENTS:  ---
#         BUGS:  ---
#        NOTES:  ---
#       AUTHOR:   (), <>
#      COMPANY:  
#      VERSION:  1.0
#      CREATED:  2009-10-22 02.51.05 CEST
#     REVISION:  ---
#===============================================================================

use strict;
use warnings;

use IO::Socket;

my $sock = new IO::Socket::UNIX( '/tmp/foo' );

$sock->say( "hello" );
while(<$sock>) {
print;
}
$sock->shutdown(2);
$sock->close;




