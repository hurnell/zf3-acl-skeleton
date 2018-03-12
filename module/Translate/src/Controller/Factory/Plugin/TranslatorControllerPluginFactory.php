<?php

/**
 * Class TranslatorControllerPluginFactory
 *
 * @package     Translate\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace Translate\Controller\Factory\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Translate\Mvc\Controller\Plugin\TranslateControllerPlugin;

/**
 * This is the factory for TranslatorControllerPluginFactory. Its purpose is to instantiate the
 * controller plugin after passing in the MvcTranslator.
 *
 * @package     Translate\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class TranslatorControllerPluginFactory implements FactoryInterface
{

    /**
     * Create/instantiate TranslateControllerPlugin object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return TranslateControllerPlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null):TranslateControllerPlugin
    {
        $translator = $container->get('MvcTranslator');
        return new TranslateControllerPlugin($translator);
    }

}
