<?php

/**
 * Get/add to application's configuration settings
 *
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUser\View\Helper;

use Zend\View\Helper\AbstractHelper;
use AclUser\Permissions\Acl\AccessControlList;

/**
 * View helper which is needed to get details about logged in user
 * in view scripts
 *
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class UserIsAllowedViewHelper extends AbstractHelper
{

    /**
     * AccessControlList Object which is needed to get details about logged in user
     * 
     * @var AccessControlList 
     */
    protected $accessControleList;

    /**
     * Constructor
     * 
     * @param AccessControlList $accessControleList
     */
    public function __construct(AccessControlList $accessControleList)
    {
        $this->accessControleList = $accessControleList;
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
        return $this->accessControleList->userIsAllowed($resource, $privilege);
    }

}
