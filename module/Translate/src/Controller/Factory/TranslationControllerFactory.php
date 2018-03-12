<?php

/**
 * Class TranslationControllerFactory
 *
 * @package     Translate\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Translate\Controller\TranslationController;
use Translate\Service\TranslationManager;

/**
 * This is the factory for TranslationController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 *
 * @package     Translate\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationControllerFactory implements FactoryInterface
{

    /**
     * This method creates the TranslationController service and returns its instance
     * after getting TranslationManager 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return TranslationController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null):TranslationController
    {
        // get translation manager to inject into the controller
        $translationManager = $container->get(TranslationManager::class);
        $languageManager = $container->get('languageManager');
        // Instantiate the controller and inject dependencies
        return new TranslationController($translationManager, $languageManager);
    }

}
