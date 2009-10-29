<?php
require_once 'PEAR.php';
require_once 'PEAR/Exception.php';
class Socket_Exception extends PEAR_Exception
{
    public function __construct($message, $errno)
    {
        socket_clear_error();
        parent::__construct($message, $errno);
    }
    public function __toString()
    {
        $error = socket_strerror($this->code);
        return __CLASS__ . ": {$this->message}  ([{$this->code}] {$error})" . PHP_EOL;
    }
}
/**
 * A wrapper for low level socket functions
 * @todo document!!!
 */
class Socket
{
    /**
     * The underlying socket resource
     * @var resource
     */
    protected $socketResource;
    public function getSocketResource()
    {
        return $this->socketResource;
    }

    /**
     * The end of line (EOL) marker used in networking environment
     * @var string
     */
    const NET_EOL = "\r\n";

    /**
     * @param resource $socket the resource to attach
     */
    protected function __construct($resource = null)
    {
        $this->socketResource = $resource;
    }

    /**
     * Creates an basic socket
     *
     * @param int $domain the protocol family to use
     * <ul>
     * <li>AF_INET</li>
     * <li>AF_INET6</li>
     * <li>AF_UNIX</li>
     * </ul>
     * @param int $type Type of comminucation to use
     * <ul>
     * <li>SOCK_STREAM</li>
     * <li>SOCK_DGRAM</li>
     * <li>SOCK_SEQPACKET</li>
     * <li>SOCK_RAW</li>
     * <li>SOCK_RDM</li>
     * </ul>
     * @param int $protocol the protocol inside the domain to use
     */
    public function create($domain, $type, $protocol)
    {
        if (!is_null($this->socketResource)) {
            return;
        }
        $this->socketResource = @socket_create($domain, $type, $protocol);
        if ($this->socketResource === false) {
            throw new Socket_Exception("Failed to create socket", socket_last_error());
        }
        return new self($socket);
    }

    /**
     * Creates an listen socket
     * @params int $port what port to listen on, 0 for any available port
     * @param int $backlog
     */
    public function create_listen($port, $backlog = null)
    {
        if (!is_null($this->socketResource)) {
            return;
        }
        if (is_null($backlog)) {
            $this->socketResource = @socket_create_listen($port);
        } else {
            $this->socketResource = @socket_create_listen($port, $backlog);
        }
        if ($this->socketResource === false) {
            throw new Socket_Exception("Failed to create listen socket", socket_last_error());
        }
    }
    /**
     * Creates a pair of sockets
     *
     */
    public static function create_pair($domain, $type, $protocol)
    {
        $created = @socket_create_pair($domain, $type, $protocol, $fd);
        if ($created === false) {
            throw new Socket_Exception("Failed to create socket pair", socket_last_error());
        }
        $ret = array(
            new self($fd[0]) ,
            new self($fd[1])
        );
        return $ret;
    }

    public function accept()
    {
        $child_socket = @socket_accept($this->socketResource);
        if ($child_socket === false) {
            throw new Socket_Exception("Failed to accept socket", socket_last_error());
        }
        return new self($child_socket);
    }

    public function bind($address, $port = null)
    {
        if (is_null($port)) {
            $result = @socket_bind($this->socketResource, $address);
        } else {
            $result = @socket_bind($this->socketResource, $address, $port);
        }
        if ($result === false) {
            throw new Socket_Exception("Failed to bind socket to address", socket_last_error());
        }
    }

    public function listen($backlog = null)
    {
        if (is_null($backlog)) {
            $result = @socket_listen($this->socketResource);
        } else {
            $result = @socket_listen($this->socketResource, $backlog);
        }
        if ($result === false) {
            throw new Socket_Exception("Failed to tell socket to listen for incomming connections", socket_last_error());
        }
    }

    public function connect($address, $port = null)
    {
        if (is_null($port)) {
            $result = @socket_connect($this->socketResource, $address);
        } else {
            $result = @socket_connect($this->socketResource, $address, $port);
        }
        if ($result === false) {
            throw new Socket_Exception("Failed to connect to address", socket_last_error());
        }
    }

    public function close()
    {
        if (@socket_close($this->socketResource) === false) {
            throw new Socket_Exception("Failed to close socket", socket_last_error());
        }
    }

    public function shutdown($how = 2)
    {
        if (@socket_shutdown($this->socketResource, $how) === false) {
            throw new Socket_Exception("Failed to shutdown socket", socket_last_error());
        }
    }

    public function read($length, $type = PHP_BINARY_READ)
    {
        $output = @socket_read($this->socketResource, $length, $type);
        if ($output === false) {
            throw new Socket_Exception("Failed to read from socket", socket_last_error());
        }
        return $output;
    }

    public function recv(&$buf, $len, $flags)
    {
        $recieved = @socket_recv($this->socketResource, &$buf, $len, $flags);
        if ($recieved === false) {
            throw new Socket_Exception("Failed to recieve from socket", socket_last_error());
        }
        return $recieved;
    }

    public function recvfrom(&$buf, $len, $flags, $addr, $port = null)
    {
        if (is_null($port)) {
            $recieved = @socket_recvfrom($this->socketResource, &$buf, $len, $flags, $addr);
        } else {
            $recieved = @socket_recvfrom($this->socketResource, &$buf, $len, $flags, $addr, $port);
        }
        if ($recieved === false) {
            throw new Socket_Exception("Failed to recieve from socket", socket_last_error());
        }
        return $recieved;
    }

    public function send($buf, $len, $flags)
    {
        $sent = @socket_send($this->socketResource, $buf, $len, $flags);
        if ($sent === false) {
            throw new Socket_Exception("Failed to send to socket", socket_last_error());
        }
        return $sent;
    }

    public function sendto($buf, $len, $flags, $addr, $port = null)
    {
        if (is_null($port)) {
            $sent = @socket_sendto($this->socketResource, $buf, $len, $flags, $addr);
        } else {
            $sent = @socket_sendto($this->socketResource, $buf, $len, $flags, $addr, $port);
        }
        if ($sent === false) {
            throw new Socket_Exception("Failed to send to socket", socket_last_error());
        }
        return $sent;
    }

    public function write($buffer, $length = null, $strict = false)
    {
        if (is_null($length)) {
            $length = strlen($buffer);
        }
        if ($strict) {
            // Strict one write call; This might send less than we wanted
            $sent = @socket_write($this->socketResource, $buffer, $length);
            if ($sent === false) {
                throw new Socket_Exception("Failed to write to socket", socket_last_error());
            }
            return $sent;
        } else {
            // Try send everything, up to $length
            $offset = 0;
            while ($offset < $length) {
                $sent = @socket_write($this->socketResource, substr($buffer, $offset) , $length - $offset);
                if ($sent === false) {
                    throw new Socket_Exception("Failed to write to socket", socket_last_error());
                }
                if ($sent == 0) {
                    // We sent 0 bytes, see this as non-exceptional and return sent so far,
                    // to not end up in a 0-byte loop
                    break;
                }
                $offset+= $sent;
            }
            return $offset;
        }
    }

    /**
     * A simple full write with an network EOL attached
     * @param string $buffer the text to write to the socket
     * @return number written, probably length of buffer
     */
    public function say($buffer)
    {
        return $this->write("$buffer" . Socket::NET_EOL);
    }

    public function set_option($level, $optname, $optval)
    {
        if (@socket_set_option($this->socketResource, $level, $optname, $optval) === false) {
            throw new Socket_Exception("Failed to set socket option", socket_last_error());
        }
    }

    public function get_option($level, $optname)
    {
        $optval = @socket_get_option($this->socketResource, $level, $optname);
        if ($optval === false) {
            throw new Socket_Exception("Failed to get socket option", socket_last_error());
        }
        return $optval;
    }

    public function set_block()
    {
        if (@socket_set_block($this->socketResource) === false) {
            throw new Socket_Exception("Failed to set socket blocking", socket_last_error());
        }
    }

    public function set_nonblock()
    {
        if (@socket_set_nonblock($this->socketResource) === false) {
            throw new Socket_Exception("Failed to set socket nonblocking", socket_last_error());
        }
    }

    public function getpeername(&$address, &$port = null)
    {
        if (is_null($port)) {
            $result = @socket_getpeername($this->socketResource, &$address);
        } else {
            $result = @socket_getpeername($this->socketResource, &$address, &$port);
        }
        if ($result === false) {
            throw new Socket_Exception("Failed to get peer name", socket_last_error());
        }
    }

    public function getsockname(&$address, &$port = null)
    {
        if (is_null($port)) {
            $result = @socket_getsockname($this->socketResource, &$address);
        } else {
            $result = @socket_getsockname($this->socketResource, &$address, &$port);
        }
        if ($result === false) {
            throw new Socket_Exception("Failed to get sock name", socket_last_error());
        }
    }
}
