<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Evenement\EventEmitter;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface;
use Exception;
final class Server extends \RectorPrefix20210620\_HumbugBox15516bb2b566\Evenement\EventEmitter implements \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\ServerInterface
{
    private $server;
    public function __construct($uri, \RectorPrefix20210620\_HumbugBox15516bb2b566\React\EventLoop\LoopInterface $loop, array $context = array())
    {
        // sanitize TCP context options if not properly wrapped
        if ($context && (!isset($context['tcp']) && !isset($context['tls']) && !isset($context['unix']))) {
            $context = array('tcp' => $context);
        }
        // apply default options if not explicitly given
        $context += array('tcp' => array(), 'tls' => array(), 'unix' => array());
        $scheme = 'tcp';
        $pos = \strpos($uri, '://');
        if ($pos !== \false) {
            $scheme = \substr($uri, 0, $pos);
        }
        if ($scheme === 'unix') {
            $server = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\UnixServer($uri, $loop, $context['unix']);
        } else {
            $server = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\TcpServer(\str_replace('tls://', '', $uri), $loop, $context['tcp']);
            if ($scheme === 'tls') {
                $server = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\SecureServer($server, $loop, $context['tls']);
            }
        }
        $this->server = $server;
        $that = $this;
        $server->on('connection', function (\RectorPrefix20210620\_HumbugBox15516bb2b566\React\Socket\ConnectionInterface $conn) use($that) {
            $that->emit('connection', array($conn));
        });
        $server->on('error', function (\Exception $error) use($that) {
            $that->emit('error', array($error));
        });
    }
    public function getAddress()
    {
        return $this->server->getAddress();
    }
    public function pause()
    {
        $this->server->pause();
    }
    public function resume()
    {
        $this->server->resume();
    }
    public function close()
    {
        $this->server->close();
    }
}
