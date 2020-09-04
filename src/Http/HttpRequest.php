<?php

/**
 * Quantum PHP Framework
 *
 * An open source software development framework for PHP
 *
 * @package Quantum
 * @author Arman Ag. <arman.ag@softberg.org>
 * @copyright Copyright (c) 2018 Softberg LLC (https://softberg.org)
 * @link http://quantum.softberg.org/
 * @since 2.0.0
 */

namespace Quantum\Http;

use Quantum\Exceptions\ExceptionMessages;
use Quantum\Exceptions\RequestException;
use Quantum\Environment\Server;
use Quantum\Bootstrap;

/**
 * Class HttpRequest
 * @package Quantum\Http
 */
abstract class HttpRequest
{

    /**
     * Request headers
     * @var array
     */
    private static $__headers = [];

    /**
     * Request body
     * @var array
     */
    private static $__request = [];

    /**
     * Files
     * @var array 
     */
    private static $__files = [];

    /**
     * Request method
     * @var string
     */
    private static $__method = null;

    /**
     * Scheme
     * @var string 
     */
    private static $__protocol = null;

    /**
     * Host name
     * @var string 
     */
    private static $__host = null;

    /**
     * Server port
     * @var string 
     */
    private static $__port = null;

    /**
     * Request URI
     * @var string
     */
    private static $__uri = null;

    /**
     * Query string
     * @var string
     */
    private static $__query = null;

    /**
     * Available methods
     * @var array 
     */
    private static $availableMetods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    /**
     * Server
     * @var \Quantum\Environment\Server 
     */
    private static $server;

    /**
     * Initialize the Request
     * @throws RequestException When called outside of Bootstrap
     */
    public static function init(Server $server)
    {
        if (get_caller_class() !== Bootstrap::class) {
            throw new RequestException(ExceptionMessages::UNEXPECTED_REQUEST_INITIALIZATION);
        }

        self::$server = $server;

        self::$__method = self::$server->method();

        self::$__protocol = self::$server->protocol();

        self::$__host = self::$server->host();

        self::$__port = self::$server->port();

        self::$__uri = self::$server->uri();

        self::$__query = self::$server->query();

        self::$__headers = array_change_key_case((array) getallheaders(), CASE_LOWER);

        self::$__request = array_merge(
                self::$__request,
                self::getParams(),
                self::postParams(),
                self::getRawInputs()
        );

        self::$__files = self::handleFiles($_FILES);
    }

    /**
     * Creates new request for internal use
     * @param string $method
     * @param string $url
     * @param array|null $file
     */
    public static function create(string $method, string $url, array $data = null, array $file = null)
    {
        $parsed = parse_url($url);

        self::setMethod($method);

        if (isset($parsed['scheme'])) {
            self::setProtocol($parsed['scheme']);
        }

        if (isset($parsed['host'])) {
            self::setHost($parsed['host']);
        }

        if (isset($parsed['port'])) {
            self::setPort($parsed['port']);
        }
        if (isset($parsed['path'])) {
            self::setUri($parsed['path']);
        }
        if (isset($parsed['query'])) {
            self::setQuery($parsed['query']);
        }

        if ($data) {
            self::$__request = $data;
        }

        if ($file) {
            self::$__files = self::handleFiles($file);
        }
    }

    /**
     * Flushes the request header , body and files
     */
    public static function flush()
    {
        self::$__headers = [];
        self::$__request = [];
        self::$__files = [];
        self::$__protocol = null;
        self::$__host = null;
        self::$__port = null;
        self::$__uri = null;
        self::$__query = null;
    }

    /**
     * Gets the request method
     * @return mixed
     */
    public static function getMethod()
    {
        return self::$__method;
    }

    /**
     * Sets the request method
     * @param string $method
     * @throws RequestException
     */
    public static function setMethod(string $method)
    {
        if (!in_array($method, self::$availableMetods)) {
            throw new RequestException();
        }

        self::$__method = $method;
    }

    /**
     * Gets the protocol
     * @return string
     */
    public static function getProtocol()
    {
        return self::$__protocol;
    }

    /**
     * Sets the protocol
     * @param string $protocol
     */
    public static function setProtocol(string $protocol)
    {
        self::$__protocol = $protocol;
    }

    /**
     * Gets the host name
     * @return string
     */
    public static function getHost()
    {
        return self::$__host;
    }

    /**
     * Sets the host name
     * @param string $host
     */
    public static function setHost(string $host)
    {   
        self::$__host = $host;
    }

    /**
     * Gets the port
     * @return string
     */
    public static function getPort()
    {
        return self::$__port;
    }

    /**
     * Sets the port
     * @param string $port
     */
    public static function setPort($port)
    {
        self::$__port = $port;
    }

    /**
     * Gets the URI
     * @return string|null
     */
    public static function getUri()
    {
        return self::$__uri;
    }

    /**
     * Sets the URI
     * @param string $uri
     */
    public static function setUri(string $uri)
    {
        self::$__uri = ltrim($uri, '/');
    }

    /**
     * Gets the query string
     * @return string
     */
    public static function getQuery()
    {
        return self::$__query;
    }

    /**
     * Sets the query string
     * @param string $query
     */
    public static function setQuery(string $query)
    {
        self::$__query = $query;
    }

    /**
     * Sets the query param
     * @param string $key
     * @param string $value
     * @return string
     */
    public static function setQueryParam(string $key, string $value)
    {
        $queryParams = self::$__query ? explode('&', self::$__query) : []; 
        array_push($queryParams, $key . '=' . $value);  
        self::$__query =  implode('&', $queryParams);   
    }

    /**
     * Gets the query param
     * @param string $key
     * @return string|null
     */
    public static function getQueryParam(string $key)
    {  
        $query = explode('&', self::$__query);    

        foreach($query as $items){
           $item = explode('=', $items); 
           if($item[0] == $key){
                return $item[1];
           }
        }

        return null;
    }

    /**
     * Sets new key/value pair into request
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value)
    {
        self::$__request[$key] = $value;
    }

    /**
     * Checks if request contains a data by given key
     * @param string $key
     * @return bool
     */
    public static function has($key)
    {
        return isset(self::$__request[$key]);
    }

    /**
     * Retrieves data from request by given key
     * @param string $key
     * @param string $default
     * @param bool $raw
     * @return mixed
     */
    public static function get($key, $default = null, $raw = false)
    {
        $data = $default;

        if (self::has($key)) {
            if ($raw) {
                $data = self::$__request[$key];
            } else {
                $data = is_array(self::$__request[$key]) ?
                        filter_var_array(self::$__request[$key], FILTER_SANITIZE_STRING) :
                        filter_var(self::$__request[$key], FILTER_SANITIZE_STRING);
            }
        }

        return $data;
    }

    /**
     * Gets all request parameters
     * @return array
     */
    public static function all()
    {
        return array_merge(self::$__request, self::$__files);
    }

    /**
     * Deletes the element from request by given key
     * @param string $key
     */
    public static function delete($key)
    {
        if (self::has($key)) {
            unset(self::$__request[$key]);
        }
    }

    /**
     * Checks to see if request contains file
     * @return bool
     */
    public static function hasFile($key)
    {
        return isset(self::$__files[$key]);
    }

    /**
     * Gets the file info by given key
     * @param string $key
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function getFile($key)
    {
        if (!self::hasFile($key)) {
            throw new \InvalidArgumentException(_message(ExceptionMessages::UPLOADED_FILE_NOT_FOUND, $key));
        }

        return self::$__files[$key];
    }

    /**
     * Sets the request header
     * @param string $key
     * @param mixed $value
     */
    public static function setHeader($key, $value)
    {
        self::$__headers[strtolower($key)] = $value;
    }

    /**
     * Checks the request header existence by given key
     * @param string $key
     * @return bool
     */
    public static function hasHeader($key)
    {
        return isset(self::$__headers[strtolower($key)]);
    }

    /**
     * Gets the request header by given key
     * @param $key
     * @return mixed|null
     */
    public static function getHeader($key)
    {
        return self::hasHeader($key) ? self::$__headers[strtolower($key)] : null;
    }

    /**
     * Gets all request headers
     * @return array
     */
    public static function allHeaders()
    {
        return self::$__headers;
    }

    /**
     * Deletes the header by given key
     * @param string $key
     */
    public static function deleteHeader($key)
    {
        if (self::hasHeader($key)) {
            unset(self::$__headers[strtolower($key)]);
        }
    }

    /**
     * Gets the nth segment
     * @param integer $number
     * @return string|null
     */
    public static function getSegment($number)
    {
        $segments = self::getAllSegments();

        if (isset($segments[$number])) {
            return $segments[$number];
        }

        return null;
    }

    /**
     * Gets the segments of current URI
     * @return array
     */
    public static function getAllSegments()
    {
        $segments = explode('/', trim(parse_url(self::$__uri)['path'], '/'));
        array_unshift($segments, 'zero_segment');
        return $segments;
    }

    /**
     * Gets Сross Site Request Forgery Token
     * @return string
     */
    public static function getCSRFToken()
    {
        $csrfToken = null;

        if (self::has('token')) {
            $csrfToken = self::get('token');
        } elseif (self::hasHeader('X-csrf-token')) {
            $csrfToken = self::getHeader('X-csrf-token');
        }

        return $csrfToken;
    }

    /**
     * Gets Authorization Bearer token
     * @return string
     */
    public static function getAuthorizationBearer()
    {
        $bearerToken = null;

        if (self::hasHeader('Authorization')) { 
            if (preg_match('/Bearer\s(\S+)/', self::getHeader('Authorization'), $matches)) {
                $bearerToken = $matches[1];
            }
        }

        return $bearerToken;
    }

    /**
     * Checks to see if request was AJAX request
     * @return boolean
     */
    public static function isAjax()
    {
        if (self::hasHeader('X-REQUESTED-WITH') || self::$server->ajax()) {
            return true;
        }

        return false;
    }

    /**
     * Gets the referrer
     * @return string
     */
    public static function getReferrer()
    {
        return self::$server->referrer();
    }

    /**
     * Gets the GET params
     * @return array
     */
    private static function getParams(): array
    {
        $getParams = [];

        if (!empty($_GET)) {
            $getParams = filter_input_array(INPUT_GET, FILTER_DEFAULT);
        }

        return $getParams;
    }

    /**
     * Gets the POST params
     * @return array
     */
    private static function postParams(): array
    {
        $postParams = [];

        if (!empty($_POST)) {
            $postParams = filter_input_array(INPUT_POST, FILTER_DEFAULT);
        }

        return $postParams;
    }

    /**
     * Get Input parameters sent via PUT, PATCH or DELETE methods
     * @return array
     */
    private static function getRawInputs(): array
    {
        $inputParams = [];

        if (in_array(self::$__method, ['PUT', 'PATCH', 'DELETE'])) {

            $input = file_get_contents('php://input');

            if (self::$server->contentType()) {
                switch (self::$server->contentType()) {
                    case 'application/x-www-form-urlencoded':
                        parse_str($input, $inputParams);
                        break;
                    case 'application/json':
                        $inputParams = json_decode($input);
                        break;
                    default :
                        $inputParams = parse_raw_http_request($input);
                        break;
                }
            }
        }

        return (array) $inputParams;
    }

    /**
     * Handles uploaded files
     */
    private static function handleFiles($_files)
    {
        if (!count($_files)) {
            return [];
        }

        $key = key($_files);

        if ($key) {
            if (!is_array($_files[$key]['name'])) {
                return [$key => (object) $_files[$key]];
            } else {
                $formattedFiles = [];

                foreach ($_files[$key]['name'] as $index => $name) {
                    $formattedFiles[$key][$index] = (object) [
                                'name' => $name,
                                'type' => $_files[$key]['type'][$index],
                                'tmp_name' => $_files[$key]['tmp_name'][$index],
                                'error' => $_files[$key]['error'][$index],
                                'size' => $_files[$key]['size'][$index],
                    ];
                }

                return $formattedFiles;
            }
        }
    }

}
