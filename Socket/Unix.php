<?php
require_once 'PEAR.php';
require_once 'PEAR/Exception.php';
require_once 'Socket.php';
class Socket_Unix extends Socket
{
    private $_local_fifo;
    public function __construct($args)
    {
        $type = SOCK_STREAM;
        $listen = null;
        $local = null;
        $peer = null;
        if (is_array($args)) {
            if (isset($args['type'])) {
                $type = $args['type'];
            }
            if (isset($args['listen'])) {
                $listen = $args['listen'];
            }
            if (isset($args['local'])) {
                $local = $args['local'];
            }
            if (isset($args['peer'])) {
                $peer = $args['peer'];
            }
        } else {
            $peer = $args;
        }
        $this->create($type, 0);
        if (!is_null($local)) {
            $this->_local_fifo = $local;
            if (file_exists($local)) {
                unlink($local);
            }
            $this->bind($local);
        }
        if (!is_null($listen) && $type != SOCK_DGRAM) {
            $this->listen($listen);
        } elseif (!is_null($peer)) {
            $this->connect($peer);
        }
    }
    public function __destruct()
    {
        if (!is_null($this->_local_fifo) && file_exists($this->_local_fifo)) {
            unlink($this->_local_fifo);
        }
    }
    public function create($type, $protocol)
    {
        parent::create(AF_UNIX, $type, $protocol);
    }
    public function create_listen($type, $protocol, $backlog = 0, $address = 0)
    {
        parent::create(AF_UNIX, $type, $protocol);
        $socket->bind($address);
        $socket->listen($backlog);
        return $socket;
    }
    public static function create_pair($type, $protocol)
    {
        return parent::create_pair(AF_UNIX, $type, $protocol);
    }
    public function bind($address)
    {
        parent::bind($address);
    }
    public function connect($address)
    {
        parent::connect($address);
    }
    public function recvfrom(&$buf, $len, $flags, $addr)
    {
        parent::recvfrom(&$buf, $len, $flags, $addr);
    }
    public function sendto($buf, $len, $flags, $addr)
    {
        parent::sendto($buf, $len, $flags, $addr);
    }
    public function getpeername()
    {
        parent::getpeername(&$address);
        return $address;
    }
    public function getsockname()
    {
        parent::getsockname(&$address);
        return $address;
    }
}
