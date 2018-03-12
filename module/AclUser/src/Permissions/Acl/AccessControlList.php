<?php

/**
 * Class AccessControlList which initialises and extends AclUser\Permissions\Acl
 * as well as defining which roles are allowed to access which resources with what privileges
 * Note that in most cases resources correspond to controllers and privileges correspond to actions
 *
 * @package     AclUser\Permissions\Acl
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Permissions\Acl;

use AclUser\Service\UserManager;
use Zend\Authentication\AuthenticationService;

/**
 * This class initialises and extends AclUser\Permissions\Acl
 * and defines which roles are allowed to access which resources with what privileges
 * 
 * @package     AclUser\Permissions\Acl
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AccessControlList extends Acl
{

    /**
     * Instantiate class 
     * 
     * @param array $resources list of resources
     * @param AuthenticationService $authService
     * @param UserManager $userManager
     * @param array $config
     */
    public function __construct($resources, AuthenticationService $authService, UserManager $userManager, $config)
    {
        $this->authService = $authService;
        $this->userManager = $userManager;
        $this->config = $config;
        $this->defineRoles();
        $this->defineResources($resources);
        $this->assignPriveleges();
        $this->joinAclToNavigation();
    }

    /**
     * Update this method in to assign privileges for roles to access resources.
     */
    protected function assignPriveleges()
    {
        $this->allow(['guest', 'basic'], ['index'], ['index', 'about', 'entry-point', 'dump-sessions', 'pdf','test-flash-messenger']);
        $this->allow(['guest'], ['user-auth'], ['login', 'test', 'register', 'reset-password', 'confirm-account']);
        $this->allow(['guest'], ['social'], null);
        $this->allow(['guest'], ['user'], ['forgotten-password']);
        $this->allow(['basic'], ['user-auth'], ['logout', 'identity', 'change-password']);
        $this->allow(['basic'], ['user'], null);
        $this->allow('base-translate', ['translate'], ['identity']);
        $this->allow(['site-language-admin'], ['translate'], ['manage-system-languages', 'ajax-update-available-languages']);
        $this->allow(['user-manager'], ['manage-users'], null); //remember 'can-access-all-user-photos'
        $this->allow(['user-manager'], ['user-auth'], ['create-new-user', 'ajax-generate-new-password']);
        $this->allow(['uber-translator'], ['translate'], $this->userManager->getAllLocales());
        $this->allow(['uber-translator'], ['translate'], ['index', 'edit', 'edit-translation']);
        $this->allow(['dutch-translator'], ['translate'], ['index', 'edit', 'edit-translation', 'nl_NL']);
    }

}
