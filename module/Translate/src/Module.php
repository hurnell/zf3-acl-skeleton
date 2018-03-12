<?php

/**
 * Class Module bootstrap Translate
 *
 * @package     Translate
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate;

use Translate\Service\TranslationManager;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\MvcEvent;

/**
 * Entry point for Module called as part of ZF3 start up
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
        $this->setLocaleAndTraslator($event, $serviceManager);
        /* ensure that translation manage has been created */
        $serviceManager->get(TranslationManager::class);
        $languageManager = $serviceManager->get('languageManager');
        // Get shared event manager.
        $sharedEventManager = $application->getEventManager()->getSharedManager();
        $sharedEventManager->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [$languageManager, 'onDispatch'], 104);
    }

    /**
     * Set locale and translator 
     * 
     * @param MvcEvent $event 
     * @param ServiceManager $serviceManager
     * @return string the locale to be used
     */
    private function setLocaleAndTraslator(MvcEvent $event, ServiceManager $serviceManager)
    {
        $router = $serviceManager->get('router');
        $request = $serviceManager->get('request');
        $matchedRoute = $router->match($request);
        /* default locale for URLs that do not have one explicitly set */
        $locale = 'en_GB';
        if (null !== $matchedRoute) {
            $locale = $matchedRoute->getParam('locale', 'en_GB');
        }
        setlocale(LC_ALL, $locale);
        $viewModel = $event->getViewModel();
        $viewModel->locale = $locale;
        $event->getRouter()->setDefaultParam('locale', $locale);
        $serviceManager->get('MvcTranslator')
                ->setLocale($locale)->setFallbackLocale($locale);
        return $locale;
    }

}
