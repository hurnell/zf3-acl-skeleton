<?php

/**
 * Class SocialProviderFactory
 *
 * @package     Social\View\Helper\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Social\View\Helper\SocialProvider;
use Social\Options\ModuleOptions;

/**
 * Class used to instantiate SocialProvider view helper and inject module options
 *
 * @package     Social\View\Helper\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialProviderFactory implements FactoryInterface {

    /**
     * Create/instantiate SocialProvider view helper object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return SocialProvider
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialProvider {
        $moduleOptions = $container->get(ModuleOptions::class);
        return new SocialProvider($moduleOptions);
    }

}
