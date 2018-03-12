<?php

/**
 * Class        UserIsAllowedViewHelperFactory
 *
 * @package     AclUser\View\Helper\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUser\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUser\View\Helper\UserIsAllowedViewHelper;

/**
 * Get an instance of UserIsAllowedViewHelper view helper
 * which is used to get view helper which is needed to get details about logged in user
 * in view scripts
 * 
 * @package     AclUser\View\Helper\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class UserIsAllowedViewHelperFactory implements FactoryInterface
{

    /**
     * Get the view helper after injecting it with the AccessControlList object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return UserIsAllowedViewHelper
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): UserIsAllowedViewHelper
    {
        $accessControlList = $container->get('AccessControlList');
        return new UserIsAllowedViewHelper($accessControlList);
    }

}
