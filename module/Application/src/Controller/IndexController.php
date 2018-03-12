<?php

/**
 * Class IndexController
 *
 * @package     Application\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Class IndexController
 * 
 * Handles basic requests like site home and about page
 *
 * @package     Application\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class IndexController extends AbstractActionController
{

    /**
     * Handle request for home page Static content
     * 
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * Handle request for about page Static content
     * 
     * @return ViewModel
     */
    public function aboutAction()
    {
        return new ViewModel();
    }

    /**
     * Redirect to home (with locale, controller and action) from request with no URL params
     * 
     * @return type
     */
    public function entryPointAction()
    {
        return $this->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
    }

}
