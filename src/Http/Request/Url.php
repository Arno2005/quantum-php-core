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
 * @since 2.4.0
 */

namespace Quantum\Http\Request;

/**
 * Trait Url
 * @package Quantum\Http\Request
 */
trait Url
{

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
     * Gets the protocol
     * @return string
     */
    public static function getProtocol(): ?string
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
    public static function getHost(): ?string
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
    public static function getPort(): ?string
    {
        return self::$__port;
    }

    /**
     * Sets the port
     * @param string $port
     */
    public static function setPort(string $port)
    {
        self::$__port = $port;
    }

    /**
     * Gets the URI
     * @return string|null
     */
    public static function getUri(): ?string
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
}