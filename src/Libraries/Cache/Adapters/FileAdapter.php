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
 * @since 2.9.5
 */

namespace Quantum\Libraries\Cache\Adapters;

use Quantum\Libraries\Storage\Factories\FileSystemFactory;
use Quantum\Libraries\Storage\FileSystem;
use Quantum\Exceptions\BaseException;
use Psr\SimpleCache\CacheInterface;
use InvalidArgumentException;

/**
 * Class FileAdapter
 * @package Quantum\Libraries\Cache
 */
class FileAdapter implements CacheInterface
{

    /**
     * @var FileSystem
     */
    private $fs;

    /**
     * @var int
     */
    private $ttl;
    
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @param array $params
     * @throws BaseException
     */
    public function __construct(array $params)
    {
        $this->fs = FileSystemFactory::get();
        $this->ttl = $params['ttl'];
        $this->prefix = $params['prefix'];
        $this->cacheDir = $params['path'];
    }

    /**
     * @inheritDoc
     */
    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            $cacheItem = $this->fs->get($this->getPath($key));

            if (!$cacheItem) {
                return $default;
            }

            try {
                return unserialize($cacheItem);
            } catch (\Exception $e) {
                $this->delete($key);
                return $default;
            }
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException(t(_message('exception.non_iterable_value', '$keys')), E_WARNING);
        }

        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function has($key): bool
    {
        $path = $this->getPath($key);

        if (!$this->fs->exists($path)) {
            return false;
        }

        if (time() - $this->fs->lastModified($path) > $this->ttl) {
            $this->delete($key);
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->fs->put($this->getPath($key), serialize($value));
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException(t(_message('exception.non_iterable_value', '$values')), E_WARNING);
        }

        $results = [];

        foreach ($values as $key => $value) {
            $results[] = $this->set($key, $value, $ttl);
        }

        return !in_array(false, $results, true);
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        $path = $this->getPath($key);

        if ($this->fs->exists($path)) {
            return $this->fs->remove($path);
        }

        return false;
    }

    /**
     * @inheritDoc
     * @throws InvalidArgumentException
     */
    public function deleteMultiple($keys): bool
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException(t(_message('exception.non_iterable_value', '$keys')), E_WARNING);
        }

        $results = [];

        foreach ($keys as $key) {
            $results[] = $this->delete($key);
        }

        return !in_array(false, $results, true);
    }

    /**
     * @inheritDoc
     */
    public function clear(): bool
    {
        if (!$this->fs->isDirectory($this->cacheDir)) {
            return false;
        }

        $files = $this->fs->glob($this->cacheDir . DS . '*');

        if (!$files) {
            return false;
        }

        foreach ($files as $file) {
            $this->fs->remove($file);
        }

        return true;
    }

    /**
     * Gets the path for given cache key
     * @param string $key
     * @return string
     */
    private function getPath(string $key): string
    {
        return $this->cacheDir . DS . sha1($this->prefix . $key);
    }
}
