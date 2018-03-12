<?php

/**
 * Class Module bootstrap Application
 *
 * @package     Application
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Application;

use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;

/**
 * Entry point for Application called as part of ZF3 start up
 *
 * @package     Application
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Module
{

    const COOKIE_LIFETIME = 60 * 20 * 1; //timeout in 20 minutes
    const SESSION_NAME = 'ZF_ACL_SKELETON_SESSION_NAME';

    /**
     * This method returns the path to module.config.php file.
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * This method is called once the MVC bootstrapping is complete.
     * 
     * @param MvcEvent $event
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        $this->instantiateSession($serviceManager);
        /* If you don't do this the logged in user will be thrown out after COOKIE_LIFETIME */
        $this->ensureThatCookieDoesNotExpire($event);
    }

    /**
     * Instantiate the session manager
     * 
     * @param ServiceManager $serviceManager
     */
    private function instantiateSession(ServiceManager $serviceManager)
    {
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one to avoid passing the 
        // session manager as a dependency to other models.
        $serviceManager->get(SessionManager::class);
    }

    /**
     * Get cookie values and rejuvenate same
     * 
     * @param MvcEvent $event
     */
    public function ensureThatCookieDoesNotExpire(MvcEvent $event)
    {
        if (php_sapi_name() != 'cli') {
            $cookie = $event->getRequest()->getCookie();
            if ($cookie && $cookie->offsetExists(self::SESSION_NAME)) {
                $value = $cookie->offsetGet(self::SESSION_NAME);
                $expires = time() + self::COOKIE_LIFETIME;
                $domain = $event->getRequest()->getUri()->getHost();
                setcookie(
                        self::SESSION_NAME, // session name
                        $value, $expires, '/', $domain, APPLICATION_ENV !== 'development', true
                );
            }
        }
    }

}
