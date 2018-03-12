<?php

/**
 * Class Module bootstrap AclUser
 *
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser;

use Zend\Mvc\MvcEvent;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Entry point for AclUser called as part of ZF3 start up
 * 
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Module
{

    /**
     * This method returns the path to module.config.php file.
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Called on bootstrap event
     * 
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        //get application
        $application = $event->getApplication();

        $serviceManager = $application->getServiceManager();
        $acl = $serviceManager->get('AccessControlList');
        // Get shared event manager.
        $sharedEventManager = $application->getEventManager()->getSharedManager();
        // Register the event listener method.  
        $sharedEventManager->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [$acl, 'onDispatch'], 102);
    }

}
