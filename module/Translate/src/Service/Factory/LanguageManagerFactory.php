<?php

/**
 * Class LanguageManagerFactory
 *
 * @package     Translate\Service\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace Translate\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Translate\Service\LanguageManager;

/**
 * This is the factory for LanguageManager. Its purpose is to instantiate the
 * service and inject dependencies into it.
 * 
 * @package     AclUser\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class LanguageManagerFactory implements FactoryInterface
{

    /**
     * Create/instantiate LanguageManager 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return 
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LanguageManager
    {
        $config = $container->get('config');

        /* language_locales array must be set in module.config */
        if (!array_key_exists('language_locales', $config)) {
           throw new \Exception('language_locales array not defined in config');
        }
        return new LanguageManager($config['language_locales']);
    }

}
