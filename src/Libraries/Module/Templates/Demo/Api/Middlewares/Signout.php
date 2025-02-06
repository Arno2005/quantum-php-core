<?php

use Quantum\Libraries\Module\ModuleManager;

$moduleManager = ModuleManager::getInstance();

return '<?php

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
 
namespace ' . $moduleManager->getBaseNamespace() . '\\' . $moduleManager->getModuleName() . '\Middlewares;

use Quantum\Middleware\QtMiddleware;
use Quantum\Http\Response;
use Quantum\Http\Request;
use Closure;

/**
 * Class Signout
 * @package Modules\Api
 */
class Signout extends QtMiddleware
{

    /**
     * @param Request $request
     * @param Response $response
     * @param Closure $next
     * @return mixed
     */
    public function apply(Request $request, Response $response, Closure $next)
    {
        if (!Request::hasHeader(\'refresh_token\')) {
            $response->json([
                \'status\' => \'error\',
                \'message\' => [t(\'validation.nonExistingRecord\', \'token\')]
            ], 422);
            
            stop();
        }

        return $next($request, $response);
    }

}';