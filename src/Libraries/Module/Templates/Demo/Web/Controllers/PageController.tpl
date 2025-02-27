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

namespace {{MODULE_NAMESPACE}}\Controllers;

use Quantum\Factory\ViewFactory;
use Quantum\Http\Response;

/**
 * Class BaseController
 * @package Modules\Web
 */
class PageController extends BaseController
{

    /**
     * Main layout
     */
    const LAYOUT = 'layouts/main';

    /**
     * Works before an action 
     * @param ViewFactory $view
     */
    public function __before(ViewFactory $view)
    {
        parent::__before($view);
    }

    /**
     * Action - display home page  
     * @param Response $response
     * @param ViewFactory $view
     */
    public function home(Response $response, ViewFactory $view)
    {
        $view->setParams([
            'title' => config()->get('app_name'),
            'langs' => config()->get('langs')
        ]);

        $response->html($view->render('pages/index'));
    }

    /**
     * Action - display about page 
     * @param Response $response
     * @param ViewFactory $view
     */
    public function about(Response $response, ViewFactory $view)
    {
        $view->setParams([
            'title' => t('common.about') . ' | ' . config()->get('app_name'),
            'langs' => config()->get('langs')
        ]);

        $response->html($view->render('pages/about'));
    }
}