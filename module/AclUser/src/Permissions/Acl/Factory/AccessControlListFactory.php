<?php

/**
 * Class AccessControlListFactory
 *
 * @package     AclUser\Permissions\Acl\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Permissions\Acl\Factory;

use Interop\Container\ContainerInterface;
use AclUser\Permissions\Acl\AccessControlList;
use Zend\Authentication\AuthenticationService;
use AclUser\Service\UserManager;

/**
 * The factory responsible for creating of AccessControlList object.
 * 
 * @package     AclUser\Permissions\Acl\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AccessControlListFactory
{

    /**
     * Create/instantiate AccessControlList object with injected dependencies
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return AccessControlList
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AccessControlList
    {
        $authService = $container->get(AuthenticationService::class);
        $userManager = $container->get(UserManager::class);
        $config = $container->get('config');
        $resources = $this->getResources($config);
        return new AccessControlList($resources, $authService, $userManager, $config);
    }

    /**
     * Get all controller aliases that have been defined with all module.comg.php files
     * 
     * @param array $config globbed configuration parameters
     * @return array
     * @throws \Exception
     */
    private function getResources($config)
    {

        if (!is_array($config) || !array_key_exists('controllers', $config) || !is_array($config['controllers']) || !array_key_exists('aliases', $config['controllers']) || !is_array($config['controllers']['aliases'])) {
            throw new \Exception('The resources system is based on controller aliases');
        }
        $resources = $config['controllers']['aliases'];
        return array_keys($resources);
    }

}
