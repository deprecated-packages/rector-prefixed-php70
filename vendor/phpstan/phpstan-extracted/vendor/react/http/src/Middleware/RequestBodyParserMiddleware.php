<?php

namespace RectorPrefix20210620\_HumbugBox15516bb2b566\React\Http\Middleware;

use RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\ServerRequestInterface;
use RectorPrefix20210620\_HumbugBox15516bb2b566\React\Http\Io\MultipartParser;
final class RequestBodyParserMiddleware
{
    private $multipart;
    /**
     * @param int|string|null $uploadMaxFilesize
     * @param int|null $maxFileUploads
     */
    public function __construct($uploadMaxFilesize = null, $maxFileUploads = null)
    {
        $this->multipart = new \RectorPrefix20210620\_HumbugBox15516bb2b566\React\Http\Io\MultipartParser($uploadMaxFilesize, $maxFileUploads);
    }
    public function __invoke(\RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\ServerRequestInterface $request, $next)
    {
        $type = \strtolower($request->getHeaderLine('Content-Type'));
        list($type) = \explode(';', $type);
        if ($type === 'application/x-www-form-urlencoded') {
            return $next($this->parseFormUrlencoded($request));
        }
        if ($type === 'multipart/form-data') {
            return $next($this->multipart->parse($request));
        }
        return $next($request);
    }
    private function parseFormUrlencoded(\RectorPrefix20210620\_HumbugBox15516bb2b566\Psr\Http\Message\ServerRequestInterface $request)
    {
        // parse string into array structure
        // ignore warnings due to excessive data structures (max_input_vars and max_input_nesting_level)
        $ret = array();
        @\parse_str((string) $request->getBody(), $ret);
        return $request->withParsedBody($ret);
    }
}
