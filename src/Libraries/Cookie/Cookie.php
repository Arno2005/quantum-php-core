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
 * @since 1.0.0
 */

namespace Quantum\Libraries\Cookie;

/**
 * Cookie Class
 *
 * @package Quantum
 * @subpackage Libraries.Cookie
 * @category Libraries
 */
class Cookie
{

    /**
     * @var CookieAdapter 
     */
    private $adapter;

    /**
     * Cookie constructor
     *
     * @param CookieAdapter $adapter
     */
    public function __construct(CookieAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Gets data from cookie by given key
     *
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->adapter->get($key);
    }

    /*
     * Gets whole cookie data
     */
    public function all()
    {
        return $this->adapter->all();
    }

    /**
     * Check if cookie contains a data by given key
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->adapter->has($key);
    }

    /**
     * Sets cookie data by given key
     *
     * @param string $key
     * @param string $value
     * @param integer $time
     * @param string $path
     * @param string $domain
     * @param boolean $secure
     * @param boolean $httponly
     * @return bool
     */
    public function set($key, $value = '', $time = 0, $path = '/', $domain = '', $secure = FALSE, $httponly = FALSE)
    {
        return $this->adapter->set($key, $value, $time, $path, $domain, $secure, $httponly);
    }

    /**
     * Delete cookie data by given key
     *
     * @param string $key
     * @param string $path
     */
    public function delete($key, $path = '/')
    {
        $this->adapter->delete($key, $path);
    }

    /**
     * Deletes whole cookie data
     */
    public function flush()
    {
        $this->adapter->flush();
    }

}
