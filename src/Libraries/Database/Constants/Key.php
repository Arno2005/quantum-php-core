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

namespace Quantum\Libraries\Database\Constants;

/**
 * Class Key
 * @package Quantum\Libraries\Database
 */
class Key
{

    /**
     * Primary key definition
     */
    const PRIMARY = 'primary';

    /**
     * Index key definition
     */
    const INDEX = 'index';

    /**
     * Unique key definition
     */
    const UNIQUE = 'unique';

    /**
     * Full-text key definition
     */
    const FULLTEXT = 'fulltext';

    /**
     * Spatial key definition
     */
    const SPATIAL = 'spatial';

}
