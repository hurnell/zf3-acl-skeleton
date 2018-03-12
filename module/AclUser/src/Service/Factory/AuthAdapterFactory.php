<?php

/**
 * Class AuthAdapterFactory
 *
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service\Factory;

use Interop\Container\ContainerInterface;
use AclUser\Service\AuthAdapter;
use Zend\ServiceManager\Factory\FactoryInterface;

/**
 * This is the factory class for AuthAdapter service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 * 
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AuthAdapterFactory implements FactoryInterface {

    /**
     * Create/instantiate AuthAdapter object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return AuthAdapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        // Get Doctrine entity manager from Service Manager.
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        // Create the AuthAdapter and inject dependency to its constructor.
        return new AuthAdapter($entityManager);
    }

}
