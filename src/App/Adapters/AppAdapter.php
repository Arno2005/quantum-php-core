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

namespace Quantum\App\Adapters;

use Quantum\Di\Exceptions\DiException;
use Quantum\Exceptions\BaseException;
use Quantum\App\Traits\AppTrait;
use ReflectionException;
use Quantum\Di\Di;

/**
 * Class AppAdapter
 * @package Quantum\App
 */
abstract class AppAdapter
{
    use AppTrait;

    /**
     * @var string
     */
    private static $baseDir;

    /**
     * @throws BaseException
     * @throws DiException
     * @throws ReflectionException
     */
    public function __construct()
    {
        Di::loadDefinitions();

        $this->loadCoreHelperFunctions();
        $this->loadLibraryHelperFunctions();
        $this->loadAppHelperFunctions();
    }
}