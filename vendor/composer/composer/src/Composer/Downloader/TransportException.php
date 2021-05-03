<?php

/*
 * This file is part of Composer.
 *
 * (c) Nils Adermann <naderman@naderman.de>
 *     Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210503\Composer\Downloader;

/**
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class TransportException extends \RuntimeException
{
    protected $headers;
    protected $response;
    protected $statusCode;
    protected $responseInfo = array();
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }
    public function getHeaders()
    {
        return $this->headers;
    }
    public function setResponse($response)
    {
        $this->response = $response;
    }
    public function getResponse()
    {
        return $this->response;
    }
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }
    public function getStatusCode()
    {
        return $this->statusCode;
    }
    /**
     * @return array
     */
    public function getResponseInfo()
    {
        return $this->responseInfo;
    }
    /**
     * @param array $responseInfo
     */
    public function setResponseInfo(array $responseInfo)
    {
        $this->responseInfo = $responseInfo;
    }
}
