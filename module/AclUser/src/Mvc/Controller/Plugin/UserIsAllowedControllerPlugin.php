<?php

/**
 * Class UserIsAllowedControllerPlugin
 *
 * @package     Application\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use AclUser\Permissions\Acl\AccessControlList;

/**
 * Controller plugin that can check whether user is allowed to access resource privilege 
 * 
 * @package     Application\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class UserIsAllowedControllerPlugin extends AbstractPlugin
{

    /**
     * AccessControlList Object which is needed to get details about logged in user
     * 
     * @var AccessControlList 
     */
    protected $accessControlList;

    /**
     * Constructor 
     * 
     * @param AccessControlList $accessControlList Object which is needed to get details about logged in user
     */
    public function __construct(AccessControlList $accessControlList)
    {
        $this->accessControlList = $accessControlList;
    }

    /**
     * Check whether current user is allowed to access this resource and privilege 
     * 
     * @param string $resource
     * @param string $privilege
     * @return boolean whether user is permitted to access this route
     */
    public function userIsAllowed($resource, $privilege)
    {
        return $this->accessControlList->userIsAllowed($resource, $privilege);
    }

    /**
     * Get logged in user's id
     * 
     * @return integer the id of the logged in user
     */
    public function getPresentUserId()
    {
        return $this->accessControlList->getPresentUserId();
    }

    /**
     * Get the entity object that corresponds to the logged in user
     * 
     * @return User
     */
    public function getPresentUser()
    {
        return $this->accessControlList->getPresentUser();
    }

}
