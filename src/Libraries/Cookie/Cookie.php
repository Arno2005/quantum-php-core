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
 * @since 2.8.0
 */

namespace Quantum\Libraries\Cookie;

use Quantum\Exceptions\CryptorException;

/**
 * Class Cookie
 * @package Quantum\Libraries\Cookie
 */
class Cookie implements CookieStorageInterface
{

    /**
     * Cookie storage
     * @var array $storage
     */
    private static $storage = [];

    /**
     * Cookie instance
     * @var Cookie|null
     */
    private static $instance = null;

    /**
     * Cookie constructor.
     */
    private function __construct()
    {
        // Preventing to create new object through constructor
    }

    /**
     *  Gets the cookie instance
     * @param array $storage
     * @return Cookie
     */
    public static function getInstance(array &$storage): Cookie
    {
        self::$storage = &$storage;

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @inheritDoc
     * @throws CryptorException
     */
    public function all(): array
    {
        $allCookies = [];

        foreach (self::$storage as $key => $value) {
            $allCookies[$key] = crypto_decode($value);
        }

        return $allCookies;
    }

    /**
     * @inheritDoc
     */
    public function has(string $key): bool
    {
        return isset(self::$storage[$key]) && !empty(self::$storage[$key]);
    }

    /**
     * @inheritDoc
     * @throws CryptorException
     */
    public function get(string $key)
    {
        return $this->has($key) ? crypto_decode(self::$storage[$key]) : null;
    }

    /**
     * @inheritDoc
     * @throws CryptorException
     */
    public function set(string $key, $value = '', int $time = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        self::$storage[$key] = crypto_encode($value);
        setcookie($key, crypto_encode($value), $time ? time() + $time : $time, $path, $domain, $secure, $httponly);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key, string $path = '/')
    {
        if ($this->has($key)) {
            unset(self::$storage[$key]);
            setcookie($key, '', time() - 3600, $path);
        }
    }

    /**
     * @inheritDoc
     */
    public function flush()
    {
        if (count(self::$storage)) {
            foreach (self::$storage as $key => $value) {
                $this->delete($key, '/');
            }
        }
    }
}
