<?php

/**
 * Class ManageUsersControllerFactory
 *
 * @package     AclUser\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUser\Controller\ManageUsersController;
use AclUser\Service\ManageUsersManager;

/**
 * This is the factory for ManageUsersControllerFactory. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 * 
 * @package     AclUser\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ManageUsersControllerFactory implements FactoryInterface
{

    /**
     * Create/instantiate ManageUsersController object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ManageUsersController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $manageUsersManager = $container->get(ManageUsersManager::class);

        // Instantiate the controller and inject dependencies
        return new ManageUsersController($manageUsersManager);
    }

}
