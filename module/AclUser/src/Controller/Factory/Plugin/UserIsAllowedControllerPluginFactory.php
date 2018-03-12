<?php

/**
 * Class UserIsAllowedControllerPluginFactory
 *
 * @package     Application\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUser\Controller\Factory\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin;

/**
 * This is the factory for UserIsAllowedControllerPlugin. Its purpose is to instantiate the
 * controller plugin.
 * 
 * 
 * @package     Application\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class UserIsAllowedControllerPluginFactory implements FactoryInterface
{

    /**
     * Create/instantiate RedirectMessagePlugin object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return UserIsAllowedControllerPlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserIsAllowedControllerPlugin
    {
        $accessControlList = $container->get('AccessControlList');
        return new UserIsAllowedControllerPlugin($accessControlList);
    }

}
