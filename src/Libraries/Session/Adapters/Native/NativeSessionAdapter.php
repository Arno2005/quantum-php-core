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

namespace Quantum\Libraries\Session\Adapters\Native;

use Quantum\Libraries\Session\Contracts\SessionStorageInterface;
use Quantum\Libraries\Session\Exceptions\SessionException;
use Quantum\Libraries\Session\Traits\SessionTrait;

/**
 * Class Session
 * @package Quantum\Libraries\Session
 */
class NativeSessionAdapter implements SessionStorageInterface
{

    use SessionTrait;

    /**
     * Session default timeout
     */
    const SESSION_TIMEOUT = 30 * 60;

    /**
     * Session params
     * @var array
     */
    private static $params = [];

    /**
     * Session storage
     * @var array $storage
     */
    private static $storage = [];

    /**
     * @param array|null $params
     * @throws SessionException
     */
    public function __construct(?array $params = null)
    {
        $this->initializeSession($params);
    }

    /**
     * @param array|null $params
     * @return void
     * @throws SessionException
     */
    protected function initializeSession(?array $params = null): void
    {
        $timeout = $params['timeout'] ?? self::SESSION_TIMEOUT;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (@session_start() === false) {
                throw SessionException::sessionNotStarted();
            }
        }

        if (isset($_SESSION['LAST_ACTIVITY']) && time() - $_SESSION['LAST_ACTIVITY'] > $timeout) {
            if (@session_destroy() === false) {
                throw SessionException::sessionNotDestroyed();
            }
        }

        $_SESSION['LAST_ACTIVITY'] = time();

        self::$params = $params;
        self::$storage = &$_SESSION;
    }
}