<?php

/**
 * Class ModuleOptionsFactory 
 *
 * @package     Social\Options\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Options\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Social\Options\ModuleOptions;

/**
 * Class ModuleOptionsFactory that initialises ModuleOptions and injects (globbed) 
 * 'social-config' configuration values provided in social-config.global.php and 
 * social-config.local.php files in autoload folder
 *
 * @package     Social\Options\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ModuleOptionsFactory implements FactoryInterface {

    /**
     * Instantiate ModuleOptions and inject configuration parameters
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return ModuleOptions
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ModuleOptions {
        $config = $container->get('configuration');
        $socialConfig = [];
        if (is_array($config) && array_key_exists('social-config', $config) && is_array($config['social-config'])) {
            $socialConfig = $config['social-config'];
        }
        return new ModuleOptions($socialConfig);
    }

}
