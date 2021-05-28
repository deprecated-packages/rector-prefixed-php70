<?php

namespace RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Client;

use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Socket\ConnectorInterface;
use RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Socket\Connector;
/**
 * @internal
 */
class Client
{
    private $connector;
    public function __construct(\RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\EventLoop\LoopInterface $loop, \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Socket\ConnectorInterface $connector = null)
    {
        if ($connector === null) {
            $connector = new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Socket\Connector($loop);
        }
        $this->connector = $connector;
    }
    public function request($method, $url, array $headers = array(), $protocolVersion = '1.0')
    {
        $requestData = new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Client\RequestData($method, $url, $headers, $protocolVersion);
        return new \RectorPrefix20210528\_HumbugBox0b2f2d5c77b8\React\Http\Client\Request($this->connector, $requestData);
    }
}
