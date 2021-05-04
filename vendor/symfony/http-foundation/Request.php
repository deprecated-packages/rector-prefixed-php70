<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210504\Symfony\Component\HttpFoundation;

use RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException;
use RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\JsonException;
use RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;
use RectorPrefix20210504\Symfony\Component\HttpFoundation\Session\SessionInterface;
// Help opcache.preload discover always-needed symbols
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\AcceptHeader::class);
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\FileBag::class);
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderBag::class);
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderUtils::class);
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag::class);
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\ParameterBag::class);
\class_exists(\RectorPrefix20210504\Symfony\Component\HttpFoundation\ServerBag::class);
/**
 * Request represents an HTTP request.
 *
 * The methods dealing with URL accept / return a raw path (% encoded):
 *   * getBasePath
 *   * getBaseUrl
 *   * getPathInfo
 *   * getRequestUri
 *   * getUri
 *   * getUriForPath
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Request
{
    const HEADER_FORWARDED = 0b1;
    // When using RFC 7239
    const HEADER_X_FORWARDED_FOR = 0b10;
    const HEADER_X_FORWARDED_HOST = 0b100;
    const HEADER_X_FORWARDED_PROTO = 0b1000;
    const HEADER_X_FORWARDED_PORT = 0b10000;
    const HEADER_X_FORWARDED_PREFIX = 0b100000;
    /** @deprecated since Symfony 5.2, use either "HEADER_X_FORWARDED_FOR | HEADER_X_FORWARDED_HOST | HEADER_X_FORWARDED_PORT | HEADER_X_FORWARDED_PROTO" or "HEADER_X_FORWARDED_AWS_ELB" or "HEADER_X_FORWARDED_TRAEFIK" constants instead. */
    const HEADER_X_FORWARDED_ALL = 0b1011110;
    // All "X-Forwarded-*" headers sent by "usual" reverse proxy
    const HEADER_X_FORWARDED_AWS_ELB = 0b11010;
    // AWS ELB doesn't send X-Forwarded-Host
    const HEADER_X_FORWARDED_TRAEFIK = 0b111110;
    // All "X-Forwarded-*" headers sent by Traefik reverse proxy
    const METHOD_HEAD = 'HEAD';
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    const METHOD_PURGE = 'PURGE';
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_TRACE = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';
    /**
     * @var string[]
     */
    protected static $trustedProxies = [];
    /**
     * @var string[]
     */
    protected static $trustedHostPatterns = [];
    /**
     * @var string[]
     */
    protected static $trustedHosts = [];
    protected static $httpMethodParameterOverride = \false;
    /**
     * Custom parameters.
     *
     * @var ParameterBag
     */
    public $attributes;
    /**
     * Request body parameters ($_POST).
     *
     * @var InputBag|ParameterBag
     */
    public $request;
    /**
     * Query string parameters ($_GET).
     *
     * @var InputBag
     */
    public $query;
    /**
     * Server and execution environment parameters ($_SERVER).
     *
     * @var ServerBag
     */
    public $server;
    /**
     * Uploaded files ($_FILES).
     *
     * @var FileBag
     */
    public $files;
    /**
     * Cookies ($_COOKIE).
     *
     * @var InputBag
     */
    public $cookies;
    /**
     * Headers (taken from the $_SERVER).
     *
     * @var HeaderBag
     */
    public $headers;
    /**
     * @var string|resource|false|null
     */
    protected $content;
    /**
     * @var array
     */
    protected $languages;
    /**
     * @var array
     */
    protected $charsets;
    /**
     * @var array
     */
    protected $encodings;
    /**
     * @var array
     */
    protected $acceptableContentTypes;
    /**
     * @var string
     */
    protected $pathInfo;
    /**
     * @var string
     */
    protected $requestUri;
    /**
     * @var string
     */
    protected $baseUrl;
    /**
     * @var string
     */
    protected $basePath;
    /**
     * @var string
     */
    protected $method;
    /**
     * @var string
     */
    protected $format;
    /**
     * @var SessionInterface|callable
     */
    protected $session;
    /**
     * @var string
     */
    protected $locale;
    /**
     * @var string
     */
    protected $defaultLocale = 'en';
    /**
     * @var array
     */
    protected static $formats;
    protected static $requestFactory;
    /**
     * @var string|null
     */
    private $preferredFormat;
    private $isHostValid = \true;
    private $isForwardedValid = \true;
    /**
     * @var bool|null
     */
    private $isSafeContentPreferred;
    private static $trustedHeaderSet = -1;
    const FORWARDED_PARAMS = [self::HEADER_X_FORWARDED_FOR => 'for', self::HEADER_X_FORWARDED_HOST => 'host', self::HEADER_X_FORWARDED_PROTO => 'proto', self::HEADER_X_FORWARDED_PORT => 'host'];
    /**
     * Names for headers that can be trusted when
     * using trusted proxies.
     *
     * The FORWARDED header is the standard as of rfc7239.
     *
     * The other headers are non-standard, but widely used
     * by popular reverse proxies (like Apache mod_proxy or Amazon EC2).
     */
    const TRUSTED_HEADERS = [self::HEADER_FORWARDED => 'FORWARDED', self::HEADER_X_FORWARDED_FOR => 'X_FORWARDED_FOR', self::HEADER_X_FORWARDED_HOST => 'X_FORWARDED_HOST', self::HEADER_X_FORWARDED_PROTO => 'X_FORWARDED_PROTO', self::HEADER_X_FORWARDED_PORT => 'X_FORWARDED_PORT', self::HEADER_X_FORWARDED_PREFIX => 'X_FORWARDED_PREFIX'];
    /**
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->initialize($query, $request, $attributes, $cookies, $files, $server, $content);
    }
    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->request = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\ParameterBag($request);
        $this->query = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag($query);
        $this->attributes = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\ParameterBag($attributes);
        $this->cookies = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag($cookies);
        $this->files = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\FileBag($files);
        $this->server = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\ServerBag($server);
        $this->headers = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderBag($this->server->getHeaders());
        $this->content = $content;
        $this->languages = null;
        $this->charsets = null;
        $this->encodings = null;
        $this->acceptableContentTypes = null;
        $this->pathInfo = null;
        $this->requestUri = null;
        $this->baseUrl = null;
        $this->basePath = null;
        $this->method = null;
        $this->format = null;
    }
    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return static
     */
    public static function createFromGlobals()
    {
        $request = self::createRequestFromFactory($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER);
        if ($_POST) {
            $request->request = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag($_POST);
        } elseif (0 === \strpos($request->headers->get('CONTENT_TYPE', ''), 'application/x-www-form-urlencoded') && \in_array(\strtoupper($request->server->get('REQUEST_METHOD', 'GET')), ['PUT', 'DELETE', 'PATCH'])) {
            \parse_str($request->getContent(), $data);
            $request->request = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag($data);
        }
        return $request;
    }
    /**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string               $uri        The URI
     * @param string               $method     The HTTP method
     * @param array                $parameters The query (GET) or request (POST) parameters
     * @param array                $cookies    The request cookies ($_COOKIE)
     * @param array                $files      The request files ($_FILES)
     * @param array                $server     The server parameters ($_SERVER)
     * @param string|resource|null $content    The raw body data
     *
     * @return static
     */
    public static function create(string $uri, string $method = 'GET', array $parameters = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $server = \array_replace(['SERVER_NAME' => 'localhost', 'SERVER_PORT' => 80, 'HTTP_HOST' => 'localhost', 'HTTP_USER_AGENT' => 'Symfony', 'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8', 'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5', 'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7', 'REMOTE_ADDR' => '127.0.0.1', 'SCRIPT_NAME' => '', 'SCRIPT_FILENAME' => '', 'SERVER_PROTOCOL' => 'HTTP/1.1', 'REQUEST_TIME' => \time(), 'REQUEST_TIME_FLOAT' => \microtime(\true)], $server);
        $server['PATH_INFO'] = '';
        $server['REQUEST_METHOD'] = \strtoupper($method);
        $components = \parse_url($uri);
        if (isset($components['host'])) {
            $server['SERVER_NAME'] = $components['host'];
            $server['HTTP_HOST'] = $components['host'];
        }
        if (isset($components['scheme'])) {
            if ('https' === $components['scheme']) {
                $server['HTTPS'] = 'on';
                $server['SERVER_PORT'] = 443;
            } else {
                unset($server['HTTPS']);
                $server['SERVER_PORT'] = 80;
            }
        }
        if (isset($components['port'])) {
            $server['SERVER_PORT'] = $components['port'];
            $server['HTTP_HOST'] .= ':' . $components['port'];
        }
        if (isset($components['user'])) {
            $server['PHP_AUTH_USER'] = $components['user'];
        }
        if (isset($components['pass'])) {
            $server['PHP_AUTH_PW'] = $components['pass'];
        }
        if (!isset($components['path'])) {
            $components['path'] = '/';
        }
        switch (\strtoupper($method)) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
                if (!isset($server['CONTENT_TYPE'])) {
                    $server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
                }
            // no break
            case 'PATCH':
                $request = $parameters;
                $query = [];
                break;
            default:
                $request = [];
                $query = $parameters;
                break;
        }
        $queryString = '';
        if (isset($components['query'])) {
            \parse_str(\html_entity_decode($components['query']), $qs);
            if ($query) {
                $query = \array_replace($qs, $query);
                $queryString = \http_build_query($query, '', '&');
            } else {
                $query = $qs;
                $queryString = $components['query'];
            }
        } elseif ($query) {
            $queryString = \http_build_query($query, '', '&');
        }
        $server['REQUEST_URI'] = $components['path'] . ('' !== $queryString ? '?' . $queryString : '');
        $server['QUERY_STRING'] = $queryString;
        return self::createRequestFromFactory($query, $request, [], $cookies, $files, $server, $content);
    }
    /**
     * Sets a callable able to create a Request instance.
     *
     * This is mainly useful when you need to override the Request class
     * to keep BC with an existing system. It should not be used for any
     * other purpose.
     * @param callable|null $callable
     */
    public static function setFactory($callable)
    {
        self::$requestFactory = $callable;
    }
    /**
     * Clones a request and overrides some of its parameters.
     *
     * @param array $query      The GET parameters
     * @param array $request    The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies    The COOKIE parameters
     * @param array $files      The FILES parameters
     * @param array $server     The SERVER parameters
     *
     * @return static
     */
    public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
    {
        $dup = clone $this;
        if (null !== $query) {
            $dup->query = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag($query);
        }
        if (null !== $request) {
            $dup->request = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\ParameterBag($request);
        }
        if (null !== $attributes) {
            $dup->attributes = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\ParameterBag($attributes);
        }
        if (null !== $cookies) {
            $dup->cookies = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\InputBag($cookies);
        }
        if (null !== $files) {
            $dup->files = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\FileBag($files);
        }
        if (null !== $server) {
            $dup->server = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\ServerBag($server);
            $dup->headers = new \RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderBag($dup->server->getHeaders());
        }
        $dup->languages = null;
        $dup->charsets = null;
        $dup->encodings = null;
        $dup->acceptableContentTypes = null;
        $dup->pathInfo = null;
        $dup->requestUri = null;
        $dup->baseUrl = null;
        $dup->basePath = null;
        $dup->method = null;
        $dup->format = null;
        if (!$dup->get('_format') && $this->get('_format')) {
            $dup->attributes->set('_format', $this->get('_format'));
        }
        if (!$dup->getRequestFormat(null)) {
            $dup->setRequestFormat($this->getRequestFormat(null));
        }
        return $dup;
    }
    /**
     * Clones the current request.
     *
     * Note that the session is not cloned as duplicated requests
     * are most of the time sub-requests of the main one.
     */
    public function __clone()
    {
        $this->query = clone $this->query;
        $this->request = clone $this->request;
        $this->attributes = clone $this->attributes;
        $this->cookies = clone $this->cookies;
        $this->files = clone $this->files;
        $this->server = clone $this->server;
        $this->headers = clone $this->headers;
    }
    /**
     * Returns the request as a string.
     *
     * @return string The request
     */
    public function __toString()
    {
        $content = $this->getContent();
        $cookieHeader = '';
        $cookies = [];
        foreach ($this->cookies as $k => $v) {
            $cookies[] = $k . '=' . $v;
        }
        if (!empty($cookies)) {
            $cookieHeader = 'Cookie: ' . \implode('; ', $cookies) . "\r\n";
        }
        return \sprintf('%s %s %s', $this->getMethod(), $this->getRequestUri(), $this->server->get('SERVER_PROTOCOL')) . "\r\n" . $this->headers . $cookieHeader . "\r\n" . $content;
    }
    /**
     * Overrides the PHP global variables according to this request instance.
     *
     * It overrides $_GET, $_POST, $_REQUEST, $_SERVER, $_COOKIE.
     * $_FILES is never overridden, see rfc1867
     */
    public function overrideGlobals()
    {
        $this->server->set('QUERY_STRING', static::normalizeQueryString(\http_build_query($this->query->all(), '', '&')));
        $_GET = $this->query->all();
        $_POST = $this->request->all();
        $_SERVER = $this->server->all();
        $_COOKIE = $this->cookies->all();
        foreach ($this->headers->all() as $key => $value) {
            $key = \strtoupper(\str_replace('-', '_', $key));
            if (\in_array($key, ['CONTENT_TYPE', 'CONTENT_LENGTH', 'CONTENT_MD5'], \true)) {
                $_SERVER[$key] = \implode(', ', $value);
            } else {
                $_SERVER['HTTP_' . $key] = \implode(', ', $value);
            }
        }
        $request = ['g' => $_GET, 'p' => $_POST, 'c' => $_COOKIE];
        $requestOrder = \ini_get('request_order') ?: \ini_get('variables_order');
        $requestOrder = \preg_replace('#[^cgp]#', '', \strtolower($requestOrder)) ?: 'gp';
        $_REQUEST = [[]];
        foreach (\str_split($requestOrder) as $order) {
            $_REQUEST[] = $request[$order];
        }
        $_REQUEST = \array_merge(...$_REQUEST);
    }
    /**
     * Sets a list of trusted proxies.
     *
     * You should only list the reverse proxies that you manage directly.
     *
     * @param array $proxies          A list of trusted proxies, the string 'REMOTE_ADDR' will be replaced with $_SERVER['REMOTE_ADDR']
     * @param int   $trustedHeaderSet A bit field of Request::HEADER_*, to set which headers to trust from your proxies
     */
    public static function setTrustedProxies(array $proxies, int $trustedHeaderSet)
    {
        if (self::HEADER_X_FORWARDED_ALL === $trustedHeaderSet) {
            trigger_deprecation('symfony/http-foundation', '5.2', 'The "HEADER_X_FORWARDED_ALL" constant is deprecated, use either "HEADER_X_FORWARDED_FOR | HEADER_X_FORWARDED_HOST | HEADER_X_FORWARDED_PORT | HEADER_X_FORWARDED_PROTO" or "HEADER_X_FORWARDED_AWS_ELB" or "HEADER_X_FORWARDED_TRAEFIK" constants instead.');
        }
        self::$trustedProxies = \array_reduce($proxies, function ($proxies, $proxy) {
            if ('REMOTE_ADDR' !== $proxy) {
                $proxies[] = $proxy;
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $proxies[] = $_SERVER['REMOTE_ADDR'];
            }
            return $proxies;
        }, []);
        self::$trustedHeaderSet = $trustedHeaderSet;
    }
    /**
     * Gets the list of trusted proxies.
     *
     * @return array An array of trusted proxies
     */
    public static function getTrustedProxies()
    {
        return self::$trustedProxies;
    }
    /**
     * Gets the set of trusted headers from trusted proxies.
     *
     * @return int A bit field of Request::HEADER_* that defines which headers are trusted from your proxies
     */
    public static function getTrustedHeaderSet()
    {
        return self::$trustedHeaderSet;
    }
    /**
     * Sets a list of trusted host patterns.
     *
     * You should only list the hosts you manage using regexs.
     *
     * @param array $hostPatterns A list of trusted host patterns
     */
    public static function setTrustedHosts(array $hostPatterns)
    {
        self::$trustedHostPatterns = \array_map(function ($hostPattern) {
            return \sprintf('{%s}i', $hostPattern);
        }, $hostPatterns);
        // we need to reset trusted hosts on trusted host patterns change
        self::$trustedHosts = [];
    }
    /**
     * Gets the list of trusted host patterns.
     *
     * @return array An array of trusted host patterns
     */
    public static function getTrustedHosts()
    {
        return self::$trustedHostPatterns;
    }
    /**
     * Normalizes a query string.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized,
     * have consistent escaping and unneeded delimiters are removed.
     *
     * @return string A normalized query string for the Request
     * @param string|null $qs
     */
    public static function normalizeQueryString($qs)
    {
        if ('' === ($qs ?? '')) {
            return '';
        }
        $qs = \RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderUtils::parseQuery($qs);
        \ksort($qs);
        return \http_build_query($qs, '', '&', \PHP_QUERY_RFC3986);
    }
    /**
     * Enables support for the _method request parameter to determine the intended HTTP method.
     *
     * Be warned that enabling this feature might lead to CSRF issues in your code.
     * Check that you are using CSRF tokens when required.
     * If the HTTP method parameter override is enabled, an html-form with method "POST" can be altered
     * and used to send a "PUT" or "DELETE" request via the _method request parameter.
     * If these methods are not protected against CSRF, this presents a possible vulnerability.
     *
     * The HTTP method can only be overridden when the real HTTP method is POST.
     */
    public static function enableHttpMethodParameterOverride()
    {
        self::$httpMethodParameterOverride = \true;
    }
    /**
     * Checks whether support for the _method request parameter is enabled.
     *
     * @return bool True when the _method request parameter is enabled, false otherwise
     */
    public static function getHttpMethodParameterOverride()
    {
        return self::$httpMethodParameterOverride;
    }
    /**
     * Gets a "parameter" value from any bag.
     *
     * This method is mainly useful for libraries that want to provide some flexibility. If you don't need the
     * flexibility in controllers, it is better to explicitly get request parameters from the appropriate
     * public property instead (attributes, query, request).
     *
     * Order of precedence: PATH (routing placeholders or custom attributes), GET, POST
     *
     * @param mixed $default The default value if the parameter key does not exist
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if ($this !== ($result = $this->attributes->get($key, $this))) {
            return $result;
        }
        if ($this->query->has($key)) {
            return $this->query->all()[$key];
        }
        if ($this->request->has($key)) {
            return $this->request->all()[$key];
        }
        return $default;
    }
    /**
     * Gets the Session.
     *
     * @return SessionInterface The session
     */
    public function getSession()
    {
        $session = $this->session;
        if (!$session instanceof \RectorPrefix20210504\Symfony\Component\HttpFoundation\Session\SessionInterface && null !== $session) {
            $this->setSession($session = $session());
        }
        if (null === $session) {
            throw new \BadMethodCallException('Session has not been set.');
        }
        return $session;
    }
    /**
     * Whether the request contains a Session which was started in one of the
     * previous requests.
     *
     * @return bool
     */
    public function hasPreviousSession()
    {
        // the check for $this->session avoids malicious users trying to fake a session cookie with proper name
        return $this->hasSession() && $this->cookies->has($this->getSession()->getName());
    }
    /**
     * Whether the request contains a Session object.
     *
     * This method does not give any information about the state of the session object,
     * like whether the session is started or not. It is just a way to check if this Request
     * is associated with a Session instance.
     *
     * @return bool true when the Request contains a Session object, false otherwise
     */
    public function hasSession()
    {
        return null !== $this->session;
    }
    public function setSession(\RectorPrefix20210504\Symfony\Component\HttpFoundation\Session\SessionInterface $session)
    {
        $this->session = $session;
    }
    /**
     * @internal
     */
    public function setSessionFactory(callable $factory)
    {
        $this->session = $factory;
    }
    /**
     * Returns the client IP addresses.
     *
     * In the returned array the most trusted IP address is first, and the
     * least trusted one last. The "real" client IP address is the last one,
     * but this is also the least trusted one. Trusted proxies are stripped.
     *
     * Use this method carefully; you should use getClientIp() instead.
     *
     * @return array The client IP addresses
     *
     * @see getClientIp()
     */
    public function getClientIps()
    {
        $ip = $this->server->get('REMOTE_ADDR');
        if (!$this->isFromTrustedProxy()) {
            return [$ip];
        }
        return $this->getTrustedValues(self::HEADER_X_FORWARDED_FOR, $ip) ?: [$ip];
    }
    /**
     * Returns the client IP address.
     *
     * This method can read the client IP address from the "X-Forwarded-For" header
     * when trusted proxies were set via "setTrustedProxies()". The "X-Forwarded-For"
     * header value is a comma+space separated list of IP addresses, the left-most
     * being the original client, and each successive proxy that passed the request
     * adding the IP address where it received the request from.
     *
     * If your reverse proxy uses a different header name than "X-Forwarded-For",
     * ("Client-Ip" for instance), configure it via the $trustedHeaderSet
     * argument of the Request::setTrustedProxies() method instead.
     *
     * @return string|null The client IP address
     *
     * @see getClientIps()
     * @see https://wikipedia.org/wiki/X-Forwarded-For
     */
    public function getClientIp()
    {
        $ipAddresses = $this->getClientIps();
        return $ipAddresses[0];
    }
    /**
     * Returns current script name.
     *
     * @return string
     */
    public function getScriptName()
    {
        return $this->server->get('SCRIPT_NAME', $this->server->get('ORIG_SCRIPT_NAME', ''));
    }
    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathInfo()
    {
        if (null === $this->pathInfo) {
            $this->pathInfo = $this->preparePathInfo();
        }
        return $this->pathInfo;
    }
    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php         returns an empty string
     *  * http://localhost/index.php/page    returns an empty string
     *  * http://localhost/web/index.php     returns '/web'
     *  * http://localhost/we%20b/index.php  returns '/we%20b'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getBasePath()
    {
        if (null === $this->basePath) {
            $this->basePath = $this->prepareBasePath();
        }
        return $this->basePath;
    }
    /**
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    public function getBaseUrl()
    {
        $trustedPrefix = '';
        // the proxy prefix must be prepended to any prefix being needed at the webserver level
        if ($this->isFromTrustedProxy() && ($trustedPrefixValues = $this->getTrustedValues(self::HEADER_X_FORWARDED_PREFIX))) {
            $trustedPrefix = \rtrim($trustedPrefixValues[0], '/');
        }
        return $trustedPrefix . $this->getBaseUrlReal();
    }
    /**
     * Returns the real base URL received by the webserver from which this request is executed.
     * The URL does not include trusted reverse proxy prefix.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    private function getBaseUrlReal()
    {
        if (null === $this->baseUrl) {
            $this->baseUrl = $this->prepareBaseUrl();
        }
        return $this->baseUrl;
    }
    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }
    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * @return int|string can be a string if fetched from the server bag
     */
    public function getPort()
    {
        if ($this->isFromTrustedProxy() && ($host = $this->getTrustedValues(self::HEADER_X_FORWARDED_PORT))) {
            $host = $host[0];
        } elseif ($this->isFromTrustedProxy() && ($host = $this->getTrustedValues(self::HEADER_X_FORWARDED_HOST))) {
            $host = $host[0];
        } elseif (!($host = $this->headers->get('HOST'))) {
            return $this->server->get('SERVER_PORT');
        }
        if ('[' === $host[0]) {
            $pos = \strpos($host, ':', \strrpos($host, ']'));
        } else {
            $pos = \strrpos($host, ':');
        }
        if (\false !== $pos && ($port = \substr($host, $pos + 1))) {
            return (int) $port;
        }
        return 'https' === $this->getScheme() ? 443 : 80;
    }
    /**
     * Returns the user.
     *
     * @return string|null
     */
    public function getUser()
    {
        return $this->headers->get('PHP_AUTH_USER');
    }
    /**
     * Returns the password.
     *
     * @return string|null
     */
    public function getPassword()
    {
        return $this->headers->get('PHP_AUTH_PW');
    }
    /**
     * Gets the user info.
     *
     * @return string A user name and, optionally, scheme-specific information about how to gain authorization to access the server
     */
    public function getUserInfo()
    {
        $userinfo = $this->getUser();
        $pass = $this->getPassword();
        if ('' != $pass) {
            $userinfo .= ":{$pass}";
        }
        return $userinfo;
    }
    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();
        if ('http' == $scheme && 80 == $port || 'https' == $scheme && 443 == $port) {
            return $this->getHost();
        }
        return $this->getHost() . ':' . $port;
    }
    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri()
    {
        if (null === $this->requestUri) {
            $this->requestUri = $this->prepareRequestUri();
        }
        return $this->requestUri;
    }
    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string The scheme and HTTP host
     */
    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }
    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string A normalized URI (URL) for the Request
     *
     * @see getQueryString()
     */
    public function getUri()
    {
        if (null !== ($qs = $this->getQueryString())) {
            $qs = '?' . $qs;
        }
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $this->getPathInfo() . $qs;
    }
    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string The normalized URI for the path
     */
    public function getUriForPath(string $path)
    {
        return $this->getSchemeAndHttpHost() . $this->getBaseUrl() . $path;
    }
    /**
     * Returns the path as relative reference from the current Request path.
     *
     * Only the URIs path component (no schema, host etc.) is relevant and must be given.
     * Both paths must be absolute and not contain relative parts.
     * Relative URLs from one resource to another are useful when generating self-contained downloadable document archives.
     * Furthermore, they can be used to reduce the link size in documents.
     *
     * Example target paths, given a base path of "/a/b/c/d":
     * - "/a/b/c/d"     -> ""
     * - "/a/b/c/"      -> "./"
     * - "/a/b/"        -> "../"
     * - "/a/b/c/other" -> "other"
     * - "/a/x/y"       -> "../../x/y"
     *
     * @return string The relative target path
     */
    public function getRelativeUriForPath(string $path)
    {
        // be sure that we are dealing with an absolute path
        if (!isset($path[0]) || '/' !== $path[0]) {
            return $path;
        }
        if ($path === ($basePath = $this->getPathInfo())) {
            return '';
        }
        $sourceDirs = \explode('/', isset($basePath[0]) && '/' === $basePath[0] ? \substr($basePath, 1) : $basePath);
        $targetDirs = \explode('/', \substr($path, 1));
        \array_pop($sourceDirs);
        $targetFile = \array_pop($targetDirs);
        foreach ($sourceDirs as $i => $dir) {
            if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
                unset($sourceDirs[$i], $targetDirs[$i]);
            } else {
                break;
            }
        }
        $targetDirs[] = $targetFile;
        $path = \str_repeat('../', \count($sourceDirs)) . \implode('/', $targetDirs);
        // A reference to the same base directory or an empty subdirectory must be prefixed with "./".
        // This also applies to a segment with a colon character (e.g., "file:colon") that cannot be used
        // as the first segment of a relative-path reference, as it would be mistaken for a scheme name
        // (see https://tools.ietf.org/html/rfc3986#section-4.2).
        return !isset($path[0]) || '/' === $path[0] || \false !== ($colonPos = \strpos($path, ':')) && ($colonPos < ($slashPos = \strpos($path, '/')) || \false === $slashPos) ? "./{$path}" : $path;
    }
    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string|null A normalized query string for the Request
     */
    public function getQueryString()
    {
        $qs = static::normalizeQueryString($this->server->get('QUERY_STRING'));
        return '' === $qs ? null : $qs;
    }
    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client protocol from the "X-Forwarded-Proto" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * @return bool
     */
    public function isSecure()
    {
        if ($this->isFromTrustedProxy() && ($proto = $this->getTrustedValues(self::HEADER_X_FORWARDED_PROTO))) {
            return \in_array(\strtolower($proto[0]), ['https', 'on', 'ssl', '1'], \true);
        }
        $https = $this->server->get('HTTPS');
        return !empty($https) && 'off' !== \strtolower($https);
    }
    /**
     * Returns the host name.
     *
     * This method can read the client host name from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * @return string
     *
     * @throws SuspiciousOperationException when the host name is invalid or not trusted
     */
    public function getHost()
    {
        if ($this->isFromTrustedProxy() && ($host = $this->getTrustedValues(self::HEADER_X_FORWARDED_HOST))) {
            $host = $host[0];
        } elseif (!($host = $this->headers->get('HOST'))) {
            if (!($host = $this->server->get('SERVER_NAME'))) {
                $host = $this->server->get('SERVER_ADDR', '');
            }
        }
        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = \strtolower(\preg_replace('/:\\d+$/', '', \trim($host)));
        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        // use preg_replace() instead of preg_match() to prevent DoS attacks with long host names
        if ($host && '' !== \preg_replace('/(?:^\\[)?[a-zA-Z0-9-:\\]_]+\\.?/', '', $host)) {
            if (!$this->isHostValid) {
                return '';
            }
            $this->isHostValid = \false;
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException(\sprintf('Invalid Host "%s".', $host));
        }
        if (\count(self::$trustedHostPatterns) > 0) {
            // to avoid host header injection attacks, you should provide a list of trusted host patterns
            if (\in_array($host, self::$trustedHosts)) {
                return $host;
            }
            foreach (self::$trustedHostPatterns as $pattern) {
                if (\preg_match($pattern, $host)) {
                    self::$trustedHosts[] = $host;
                    return $host;
                }
            }
            if (!$this->isHostValid) {
                return '';
            }
            $this->isHostValid = \false;
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException(\sprintf('Untrusted Host "%s".', $host));
        }
        return $host;
    }
    /**
     * Sets the request method.
     */
    public function setMethod(string $method)
    {
        $this->method = null;
        $this->server->set('REQUEST_METHOD', $method);
    }
    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string The request method
     *
     * @see getRealMethod()
     */
    public function getMethod()
    {
        if (null !== $this->method) {
            return $this->method;
        }
        $this->method = \strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
        if ('POST' !== $this->method) {
            return $this->method;
        }
        $method = $this->headers->get('X-HTTP-METHOD-OVERRIDE');
        if (!$method && self::$httpMethodParameterOverride) {
            $method = $this->request->get('_method', $this->query->get('_method', 'POST'));
        }
        if (!\is_string($method)) {
            return $this->method;
        }
        $method = \strtoupper($method);
        if (\in_array($method, ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'PATCH', 'PURGE', 'TRACE'], \true)) {
            return $this->method = $method;
        }
        if (!\preg_match('/^[A-Z]++$/D', $method)) {
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException(\sprintf('Invalid method override "%s".', $method));
        }
        return $this->method = $method;
    }
    /**
     * Gets the "real" request method.
     *
     * @return string The request method
     *
     * @see getMethod()
     */
    public function getRealMethod()
    {
        return \strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
    }
    /**
     * Gets the mime type associated with the format.
     *
     * @return string|null The associated mime type (null if not found)
     */
    public function getMimeType(string $format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }
        return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
    }
    /**
     * Gets the mime types associated with the format.
     *
     * @return array The associated mime types
     */
    public static function getMimeTypes(string $format)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }
        return static::$formats[$format] ?? [];
    }
    /**
     * Gets the format associated with the mime type.
     *
     * @return string|null The format (null if not found)
     * @param string|null $mimeType
     */
    public function getFormat($mimeType)
    {
        $canonicalMimeType = null;
        if (\false !== ($pos = \strpos($mimeType, ';'))) {
            $canonicalMimeType = \trim(\substr($mimeType, 0, $pos));
        }
        if (null === static::$formats) {
            static::initializeFormats();
        }
        foreach (static::$formats as $format => $mimeTypes) {
            if (\in_array($mimeType, (array) $mimeTypes)) {
                return $format;
            }
            if (null !== $canonicalMimeType && \in_array($canonicalMimeType, (array) $mimeTypes)) {
                return $format;
            }
        }
        return null;
    }
    /**
     * Associates a format with mime types.
     *
     * @param string|array $mimeTypes The associated mime types (the preferred one must be the first as it will be used as the content type)
     * @param string|null $format
     */
    public function setFormat($format, $mimeTypes)
    {
        if (null === static::$formats) {
            static::initializeFormats();
        }
        static::$formats[$format] = \is_array($mimeTypes) ? $mimeTypes : [$mimeTypes];
    }
    /**
     * Gets the request format.
     *
     * Here is the process to determine the format:
     *
     *  * format defined by the user (with setRequestFormat())
     *  * _format request attribute
     *  * $default
     *
     * @see getPreferredFormat
     *
     * @return string|null The request format
     * @param string|null $default
     */
    public function getRequestFormat($default = 'html')
    {
        if (null === $this->format) {
            $this->format = $this->attributes->get('_format');
        }
        return null === $this->format ? $default : $this->format;
    }
    /**
     * Sets the request format.
     * @param string|null $format
     */
    public function setRequestFormat($format)
    {
        $this->format = $format;
    }
    /**
     * Gets the format associated with the request.
     *
     * @return string|null The format (null if no content type is present)
     */
    public function getContentType()
    {
        return $this->getFormat($this->headers->get('CONTENT_TYPE', ''));
    }
    /**
     * Sets the default locale.
     */
    public function setDefaultLocale(string $locale)
    {
        $this->defaultLocale = $locale;
        if (null === $this->locale) {
            $this->setPhpDefaultLocale($locale);
        }
    }
    /**
     * Get the default locale.
     *
     * @return string
     */
    public function getDefaultLocale()
    {
        return $this->defaultLocale;
    }
    /**
     * Sets the locale.
     */
    public function setLocale(string $locale)
    {
        $this->setPhpDefaultLocale($this->locale = $locale);
    }
    /**
     * Get the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return null === $this->locale ? $this->defaultLocale : $this->locale;
    }
    /**
     * Checks if the request method is of specified type.
     *
     * @param string $method Uppercase request method (GET, POST etc)
     *
     * @return bool
     */
    public function isMethod(string $method)
    {
        return $this->getMethod() === \strtoupper($method);
    }
    /**
     * Checks whether or not the method is safe.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.2.1
     *
     * @return bool
     */
    public function isMethodSafe()
    {
        return \in_array($this->getMethod(), ['GET', 'HEAD', 'OPTIONS', 'TRACE']);
    }
    /**
     * Checks whether or not the method is idempotent.
     *
     * @return bool
     */
    public function isMethodIdempotent()
    {
        return \in_array($this->getMethod(), ['HEAD', 'GET', 'PUT', 'DELETE', 'TRACE', 'OPTIONS', 'PURGE']);
    }
    /**
     * Checks whether the method is cacheable or not.
     *
     * @see https://tools.ietf.org/html/rfc7231#section-4.2.3
     *
     * @return bool True for GET and HEAD, false otherwise
     */
    public function isMethodCacheable()
    {
        return \in_array($this->getMethod(), ['GET', 'HEAD']);
    }
    /**
     * Returns the protocol version.
     *
     * If the application is behind a proxy, the protocol version used in the
     * requests between the client and the proxy and between the proxy and the
     * server might be different. This returns the former (from the "Via" header)
     * if the proxy is trusted (see "setTrustedProxies()"), otherwise it returns
     * the latter (from the "SERVER_PROTOCOL" server parameter).
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        if ($this->isFromTrustedProxy()) {
            \preg_match('~^(HTTP/)?([1-9]\\.[0-9]) ~', $this->headers->get('Via'), $matches);
            if ($matches) {
                return 'HTTP/' . $matches[2];
            }
        }
        return $this->server->get('SERVER_PROTOCOL');
    }
    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream
     */
    public function getContent(bool $asResource = \false)
    {
        $currentContentIsResource = \is_resource($this->content);
        if (\true === $asResource) {
            if ($currentContentIsResource) {
                \rewind($this->content);
                return $this->content;
            }
            // Content passed in parameter (test)
            if (\is_string($this->content)) {
                $resource = \fopen('php://temp', 'r+');
                \fwrite($resource, $this->content);
                \rewind($resource);
                return $resource;
            }
            $this->content = \false;
            return \fopen('php://input', 'r');
        }
        if ($currentContentIsResource) {
            \rewind($this->content);
            return \stream_get_contents($this->content);
        }
        if (null === $this->content || \false === $this->content) {
            $this->content = \file_get_contents('php://input');
        }
        return $this->content;
    }
    /**
     * Gets the request body decoded as array, typically from a JSON payload.
     *
     * @throws JsonException When the body cannot be decoded to an array
     *
     * @return array
     */
    public function toArray()
    {
        if ('' === ($content = $this->getContent())) {
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\JsonException('Request body is empty.');
        }
        try {
            $content = \json_decode($content, \true, 512, \JSON_BIGINT_AS_STRING | (\PHP_VERSION_ID >= 70300 ? \JSON_THROW_ON_ERROR : 0));
        } catch (\JsonException $e) {
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\JsonException('Could not decode request body.', $e->getCode(), $e);
        }
        if (\PHP_VERSION_ID < 70300 && \JSON_ERROR_NONE !== \json_last_error()) {
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\JsonException('Could not decode request body: ' . \json_last_error_msg(), \json_last_error());
        }
        if (!\is_array($content)) {
            throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\JsonException(\sprintf('JSON content was expected to decode to an array, "%s" returned.', \get_debug_type($content)));
        }
        return $content;
    }
    /**
     * Gets the Etags.
     *
     * @return array The entity tags
     */
    public function getETags()
    {
        return \preg_split('/\\s*,\\s*/', $this->headers->get('if_none_match', ''), -1, \PREG_SPLIT_NO_EMPTY);
    }
    /**
     * @return bool
     */
    public function isNoCache()
    {
        return $this->headers->hasCacheControlDirective('no-cache') || 'no-cache' == $this->headers->get('Pragma');
    }
    /**
     * Gets the preferred format for the response by inspecting, in the following order:
     *   * the request format set using setRequestFormat;
     *   * the values of the Accept HTTP header.
     *
     * Note that if you use this method, you should send the "Vary: Accept" header
     * in the response to prevent any issues with intermediary HTTP caches.
     * @param string|null $default
     * @return string|null
     */
    public function getPreferredFormat($default = 'html')
    {
        if (null !== $this->preferredFormat || null !== ($this->preferredFormat = $this->getRequestFormat(null))) {
            return $this->preferredFormat;
        }
        foreach ($this->getAcceptableContentTypes() as $mimeType) {
            if ($this->preferredFormat = $this->getFormat($mimeType)) {
                return $this->preferredFormat;
            }
        }
        return $default;
    }
    /**
     * Returns the preferred language.
     *
     * @param string[] $locales An array of ordered available locales
     *
     * @return string|null The preferred locale
     */
    public function getPreferredLanguage(array $locales = null)
    {
        $preferredLanguages = $this->getLanguages();
        if (empty($locales)) {
            return $preferredLanguages[0] ?? null;
        }
        if (!$preferredLanguages) {
            return $locales[0];
        }
        $extendedPreferredLanguages = [];
        foreach ($preferredLanguages as $language) {
            $extendedPreferredLanguages[] = $language;
            if (\false !== ($position = \strpos($language, '_'))) {
                $superLanguage = \substr($language, 0, $position);
                if (!\in_array($superLanguage, $preferredLanguages)) {
                    $extendedPreferredLanguages[] = $superLanguage;
                }
            }
        }
        $preferredLanguages = \array_values(\array_intersect($extendedPreferredLanguages, $locales));
        return $preferredLanguages[0] ?? $locales[0];
    }
    /**
     * Gets a list of languages acceptable by the client browser.
     *
     * @return array Languages ordered in the user browser preferences
     */
    public function getLanguages()
    {
        if (null !== $this->languages) {
            return $this->languages;
        }
        $languages = \RectorPrefix20210504\Symfony\Component\HttpFoundation\AcceptHeader::fromString($this->headers->get('Accept-Language'))->all();
        $this->languages = [];
        foreach ($languages as $lang => $acceptHeaderItem) {
            if (\false !== \strpos($lang, '-')) {
                $codes = \explode('-', $lang);
                if ('i' === $codes[0]) {
                    // Language not listed in ISO 639 that are not variants
                    // of any listed language, which can be registered with the
                    // i-prefix, such as i-cherokee
                    if (\count($codes) > 1) {
                        $lang = $codes[1];
                    }
                } else {
                    for ($i = 0, $max = \count($codes); $i < $max; ++$i) {
                        if (0 === $i) {
                            $lang = \strtolower($codes[0]);
                        } else {
                            $lang .= '_' . \strtoupper($codes[$i]);
                        }
                    }
                }
            }
            $this->languages[] = $lang;
        }
        return $this->languages;
    }
    /**
     * Gets a list of charsets acceptable by the client browser.
     *
     * @return array List of charsets in preferable order
     */
    public function getCharsets()
    {
        if (null !== $this->charsets) {
            return $this->charsets;
        }
        return $this->charsets = \array_keys(\RectorPrefix20210504\Symfony\Component\HttpFoundation\AcceptHeader::fromString($this->headers->get('Accept-Charset'))->all());
    }
    /**
     * Gets a list of encodings acceptable by the client browser.
     *
     * @return array List of encodings in preferable order
     */
    public function getEncodings()
    {
        if (null !== $this->encodings) {
            return $this->encodings;
        }
        return $this->encodings = \array_keys(\RectorPrefix20210504\Symfony\Component\HttpFoundation\AcceptHeader::fromString($this->headers->get('Accept-Encoding'))->all());
    }
    /**
     * Gets a list of content types acceptable by the client browser.
     *
     * @return array List of content types in preferable order
     */
    public function getAcceptableContentTypes()
    {
        if (null !== $this->acceptableContentTypes) {
            return $this->acceptableContentTypes;
        }
        return $this->acceptableContentTypes = \array_keys(\RectorPrefix20210504\Symfony\Component\HttpFoundation\AcceptHeader::fromString($this->headers->get('Accept'))->all());
    }
    /**
     * Returns true if the request is an XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool true if the request is an XMLHttpRequest, false otherwise
     */
    public function isXmlHttpRequest()
    {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }
    /**
     * Checks whether the client browser prefers safe content or not according to RFC8674.
     *
     * @see https://tools.ietf.org/html/rfc8674
     */
    public function preferSafeContent() : bool
    {
        if (null !== $this->isSafeContentPreferred) {
            return $this->isSafeContentPreferred;
        }
        if (!$this->isSecure()) {
            // see https://tools.ietf.org/html/rfc8674#section-3
            $this->isSafeContentPreferred = \false;
            return $this->isSafeContentPreferred;
        }
        $this->isSafeContentPreferred = \RectorPrefix20210504\Symfony\Component\HttpFoundation\AcceptHeader::fromString($this->headers->get('Prefer'))->has('safe');
        return $this->isSafeContentPreferred;
    }
    /*
     * The following methods are derived from code of the Zend Framework (1.10dev - 2010-01-24)
     *
     * Code subject to the new BSD license (https://framework.zend.com/license).
     *
     * Copyright (c) 2005-2010 Zend Technologies USA Inc. (https://www.zend.com/)
     */
    protected function prepareRequestUri()
    {
        $requestUri = '';
        if ('1' == $this->server->get('IIS_WasUrlRewritten') && '' != $this->server->get('UNENCODED_URL')) {
            // IIS7 with URL Rewrite: make sure we get the unencoded URL (double slash problem)
            $requestUri = $this->server->get('UNENCODED_URL');
            $this->server->remove('UNENCODED_URL');
            $this->server->remove('IIS_WasUrlRewritten');
        } elseif ($this->server->has('REQUEST_URI')) {
            $requestUri = $this->server->get('REQUEST_URI');
            if ('' !== $requestUri && '/' === $requestUri[0]) {
                // To only use path and query remove the fragment.
                if (\false !== ($pos = \strpos($requestUri, '#'))) {
                    $requestUri = \substr($requestUri, 0, $pos);
                }
            } else {
                // HTTP proxy reqs setup request URI with scheme and host [and port] + the URL path,
                // only use URL path.
                $uriComponents = \parse_url($requestUri);
                if (isset($uriComponents['path'])) {
                    $requestUri = $uriComponents['path'];
                }
                if (isset($uriComponents['query'])) {
                    $requestUri .= '?' . $uriComponents['query'];
                }
            }
        } elseif ($this->server->has('ORIG_PATH_INFO')) {
            // IIS 5.0, PHP as CGI
            $requestUri = $this->server->get('ORIG_PATH_INFO');
            if ('' != $this->server->get('QUERY_STRING')) {
                $requestUri .= '?' . $this->server->get('QUERY_STRING');
            }
            $this->server->remove('ORIG_PATH_INFO');
        }
        // normalize the request URI to ease creating sub-requests from this request
        $this->server->set('REQUEST_URI', $requestUri);
        return $requestUri;
    }
    /**
     * Prepares the base URL.
     *
     * @return string
     */
    protected function prepareBaseUrl()
    {
        $filename = \basename($this->server->get('SCRIPT_FILENAME', ''));
        if (\basename($this->server->get('SCRIPT_NAME', '')) === $filename) {
            $baseUrl = $this->server->get('SCRIPT_NAME');
        } elseif (\basename($this->server->get('PHP_SELF', '')) === $filename) {
            $baseUrl = $this->server->get('PHP_SELF');
        } elseif (\basename($this->server->get('ORIG_SCRIPT_NAME', '')) === $filename) {
            $baseUrl = $this->server->get('ORIG_SCRIPT_NAME');
            // 1and1 shared hosting compatibility
        } else {
            // Backtrack up the script_filename to find the portion matching
            // php_self
            $path = $this->server->get('PHP_SELF', '');
            $file = $this->server->get('SCRIPT_FILENAME', '');
            $segs = \explode('/', \trim($file, '/'));
            $segs = \array_reverse($segs);
            $index = 0;
            $last = \count($segs);
            $baseUrl = '';
            do {
                $seg = $segs[$index];
                $baseUrl = '/' . $seg . $baseUrl;
                ++$index;
            } while ($last > $index && \false !== ($pos = \strpos($path, $baseUrl)) && 0 != $pos);
        }
        // Does the baseUrl have anything in common with the request_uri?
        $requestUri = $this->getRequestUri();
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/' . $requestUri;
        }
        if ($baseUrl && null !== ($prefix = $this->getUrlencodedPrefix($requestUri, $baseUrl))) {
            // full $baseUrl matches
            return $prefix;
        }
        if ($baseUrl && null !== ($prefix = $this->getUrlencodedPrefix($requestUri, \rtrim(\dirname($baseUrl), '/' . \DIRECTORY_SEPARATOR) . '/'))) {
            // directory portion of $baseUrl matches
            return \rtrim($prefix, '/' . \DIRECTORY_SEPARATOR);
        }
        $truncatedRequestUri = $requestUri;
        if (\false !== ($pos = \strpos($requestUri, '?'))) {
            $truncatedRequestUri = \substr($requestUri, 0, $pos);
        }
        $basename = \basename($baseUrl ?? '');
        if (empty($basename) || !\strpos(\rawurldecode($truncatedRequestUri), $basename)) {
            // no match whatsoever; set it blank
            return '';
        }
        // If using mod_rewrite or ISAPI_Rewrite strip the script filename
        // out of baseUrl. $pos !== 0 makes sure it is not matching a value
        // from PATH_INFO or QUERY_STRING
        if (\strlen($requestUri) >= \strlen($baseUrl) && \false !== ($pos = \strpos($requestUri, $baseUrl)) && 0 !== $pos) {
            $baseUrl = \substr($requestUri, 0, $pos + \strlen($baseUrl));
        }
        return \rtrim($baseUrl, '/' . \DIRECTORY_SEPARATOR);
    }
    /**
     * Prepares the base path.
     *
     * @return string base path
     */
    protected function prepareBasePath()
    {
        $baseUrl = $this->getBaseUrl();
        if (empty($baseUrl)) {
            return '';
        }
        $filename = \basename($this->server->get('SCRIPT_FILENAME'));
        if (\basename($baseUrl) === $filename) {
            $basePath = \dirname($baseUrl);
        } else {
            $basePath = $baseUrl;
        }
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $basePath = \str_replace('\\', '/', $basePath);
        }
        return \rtrim($basePath, '/');
    }
    /**
     * Prepares the path info.
     *
     * @return string path info
     */
    protected function preparePathInfo()
    {
        if (null === ($requestUri = $this->getRequestUri())) {
            return '/';
        }
        // Remove the query string from REQUEST_URI
        if (\false !== ($pos = \strpos($requestUri, '?'))) {
            $requestUri = \substr($requestUri, 0, $pos);
        }
        if ('' !== $requestUri && '/' !== $requestUri[0]) {
            $requestUri = '/' . $requestUri;
        }
        if (null === ($baseUrl = $this->getBaseUrlReal())) {
            return $requestUri;
        }
        $pathInfo = \substr($requestUri, \strlen($baseUrl));
        if (\false === $pathInfo || '' === $pathInfo) {
            // If substr() returns false then PATH_INFO is set to an empty string
            return '/';
        }
        return (string) $pathInfo;
    }
    /**
     * Initializes HTTP request formats.
     */
    protected static function initializeFormats()
    {
        static::$formats = ['html' => ['text/html', 'application/xhtml+xml'], 'txt' => ['text/plain'], 'js' => ['application/javascript', 'application/x-javascript', 'text/javascript'], 'css' => ['text/css'], 'json' => ['application/json', 'application/x-json'], 'jsonld' => ['application/ld+json'], 'xml' => ['text/xml', 'application/xml', 'application/x-xml'], 'rdf' => ['application/rdf+xml'], 'atom' => ['application/atom+xml'], 'rss' => ['application/rss+xml'], 'form' => ['application/x-www-form-urlencoded']];
    }
    /**
     * @return void
     */
    private function setPhpDefaultLocale(string $locale)
    {
        // if either the class Locale doesn't exist, or an exception is thrown when
        // setting the default locale, the intl module is not installed, and
        // the call can be ignored:
        try {
            if (\class_exists(\Locale::class, \false)) {
                \Locale::setDefault($locale);
            }
        } catch (\Exception $e) {
        }
    }
    /**
     * Returns the prefix as encoded in the string when the string starts with
     * the given prefix, null otherwise.
     * @return string|null
     */
    private function getUrlencodedPrefix(string $string, string $prefix)
    {
        if (0 !== \strpos(\rawurldecode($string), $prefix)) {
            return null;
        }
        $len = \strlen($prefix);
        if (\preg_match(\sprintf('#^(%%[[:xdigit:]]{2}|.){%d}#', $len), $string, $match)) {
            return $match[0];
        }
        return null;
    }
    /**
     * @return $this
     */
    private static function createRequestFromFactory(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        if (self::$requestFactory) {
            $request = (self::$requestFactory)($query, $request, $attributes, $cookies, $files, $server, $content);
            if (!$request instanceof self) {
                throw new \LogicException('The Request factory must return an instance of Symfony\\Component\\HttpFoundation\\Request.');
            }
            return $request;
        }
        return new static($query, $request, $attributes, $cookies, $files, $server, $content);
    }
    /**
     * Indicates whether this request originated from a trusted proxy.
     *
     * This can be useful to determine whether or not to trust the
     * contents of a proxy-specific header.
     *
     * @return bool true if the request came from a trusted proxy, false otherwise
     */
    public function isFromTrustedProxy()
    {
        return self::$trustedProxies && \RectorPrefix20210504\Symfony\Component\HttpFoundation\IpUtils::checkIp($this->server->get('REMOTE_ADDR', ''), self::$trustedProxies);
    }
    private function getTrustedValues(int $type, string $ip = null) : array
    {
        $clientValues = [];
        $forwardedValues = [];
        if (self::$trustedHeaderSet & $type && $this->headers->has(self::TRUSTED_HEADERS[$type])) {
            foreach (\explode(',', $this->headers->get(self::TRUSTED_HEADERS[$type])) as $v) {
                $clientValues[] = (self::HEADER_X_FORWARDED_PORT === $type ? '0.0.0.0:' : '') . \trim($v);
            }
        }
        if (self::$trustedHeaderSet & self::HEADER_FORWARDED && isset(self::FORWARDED_PARAMS[$type]) && $this->headers->has(self::TRUSTED_HEADERS[self::HEADER_FORWARDED])) {
            $forwarded = $this->headers->get(self::TRUSTED_HEADERS[self::HEADER_FORWARDED]);
            $parts = \RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderUtils::split($forwarded, ',;=');
            $forwardedValues = [];
            $param = self::FORWARDED_PARAMS[$type];
            foreach ($parts as $subParts) {
                if (null === ($v = \RectorPrefix20210504\Symfony\Component\HttpFoundation\HeaderUtils::combine($subParts)[$param] ?? null)) {
                    continue;
                }
                if (self::HEADER_X_FORWARDED_PORT === $type) {
                    if (']' === \substr($v, -1) || \false === ($v = \strrchr($v, ':'))) {
                        $v = $this->isSecure() ? ':443' : ':80';
                    }
                    $v = '0.0.0.0' . $v;
                }
                $forwardedValues[] = $v;
            }
        }
        if (null !== $ip) {
            $clientValues = $this->normalizeAndFilterClientIps($clientValues, $ip);
            $forwardedValues = $this->normalizeAndFilterClientIps($forwardedValues, $ip);
        }
        if ($forwardedValues === $clientValues || !$clientValues) {
            return $forwardedValues;
        }
        if (!$forwardedValues) {
            return $clientValues;
        }
        if (!$this->isForwardedValid) {
            return null !== $ip ? ['0.0.0.0', $ip] : [];
        }
        $this->isForwardedValid = \false;
        throw new \RectorPrefix20210504\Symfony\Component\HttpFoundation\Exception\ConflictingHeadersException(\sprintf('The request has both a trusted "%s" header and a trusted "%s" header, conflicting with each other. You should either configure your proxy to remove one of them, or configure your project to distrust the offending one.', self::TRUSTED_HEADERS[self::HEADER_FORWARDED], self::TRUSTED_HEADERS[$type]));
    }
    private function normalizeAndFilterClientIps(array $clientIps, string $ip) : array
    {
        if (!$clientIps) {
            return [];
        }
        $clientIps[] = $ip;
        // Complete the IP chain with the IP the request actually came from
        $firstTrustedIp = null;
        foreach ($clientIps as $key => $clientIp) {
            if (\strpos($clientIp, '.')) {
                // Strip :port from IPv4 addresses. This is allowed in Forwarded
                // and may occur in X-Forwarded-For.
                $i = \strpos($clientIp, ':');
                if ($i) {
                    $clientIps[$key] = $clientIp = \substr($clientIp, 0, $i);
                }
            } elseif (0 === \strpos($clientIp, '[')) {
                // Strip brackets and :port from IPv6 addresses.
                $i = \strpos($clientIp, ']', 1);
                $clientIps[$key] = $clientIp = \substr($clientIp, 1, $i - 1);
            }
            if (!\filter_var($clientIp, \FILTER_VALIDATE_IP)) {
                unset($clientIps[$key]);
                continue;
            }
            if (\RectorPrefix20210504\Symfony\Component\HttpFoundation\IpUtils::checkIp($clientIp, self::$trustedProxies)) {
                unset($clientIps[$key]);
                // Fallback to this when the client IP falls into the range of trusted proxies
                if (null === $firstTrustedIp) {
                    $firstTrustedIp = $clientIp;
                }
            }
        }
        // Now the IP chain contains only untrusted proxies and the client IP
        return $clientIps ? \array_reverse($clientIps) : [$firstTrustedIp];
    }
}
