<?php

/**
 * Class SocialManagerFactory
 *
 * @package     Social\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Social\Service\SocialManager;
use Social\Options\ModuleOptions;
use Social\Service\SocialAuthManager;

/**
 * This is the factory class for SocialManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 *
 * @package     Social\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialManagerFactory implements FactoryInterface {

    /**
     * Create/instantiate SocialManager object 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return SocialManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialManager {
        $moduleOptions = $container->get(ModuleOptions::class);
        $socialAuthManager = $container->get(SocialAuthManager::class);
        $sessionContainer = $container->get('social_saved_state');
        return new SocialManager($moduleOptions, $socialAuthManager, $sessionContainer);
    }

}
