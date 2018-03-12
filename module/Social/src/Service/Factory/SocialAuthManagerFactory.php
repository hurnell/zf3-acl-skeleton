<?php

/**
 * Class SocialAuthManagerFactory
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
use Social\Options\ModuleOptions;
use Social\Service\SocialAuthManager;
use AclUser\Service\UserManager;

/**
 * This is the factory class for SocialAuthManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 *
 * @package     Social\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialAuthManagerFactory implements FactoryInterface {

    /**
     * Create/instantiate SocialAuthManager object 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return SocialManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialAuthManager {

        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $moduleOptions = $container->get(ModuleOptions::class);
        $userClass = $moduleOptions->getDoctrineUserEntity();
        $authService = $container->get($moduleOptions->getAuthenticationService());
        $userManager = $container->get(UserManager::class);
        return new SocialAuthManager($entityManager, $userClass, $authService, $userManager);
    }

}
