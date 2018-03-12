<?php

/**
 * Class Acl
 *
 * @package     AclUser\Permissions\Acl
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Permissions\Acl;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl as ZendAcl;
use Zend\View\Helper\Navigation;
use Zend\Router\Http\RouteMatch;

/**
 * Class Acl
 * 
 * Extends Zend\Acl
 * 
 * @package     AclUser\Permissions\Acl
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Acl extends ZendAcl
{

    /**
     * An array that stores the role name under the role's id as key
     * 
     * @var array
     */
    protected $roleMemory = [];

    /**
     * Array (strings) of all roles that exist in database
     *  
     * @var array 
     */
    protected $allRoles;

    /**
     * Configuration array 
     * 
     * @var array
     */
    protected $config;

    /**
     * Authentication and Identity management helper
     * 
     * @var Zend\Authentication\AuthenticationService 
     */
    protected $authService;

    /**
     * UserManager that handles logic for a registered User
     * 
     * @var AclUser\Service\UserManager
     */
    protected $userManager;

    /**
     * add all roles used by the application to the access control list parent object
     */
    protected function defineRoles()
    {
        $this->addRole('guest');
        $this->allRoles = ['guest'];

        $roles = $this->userManager->fetchAllRoles();
        foreach ($roles as $role) {
            $this->allRoles[] = $role->getName();
            $this->checkAddParentRole($role);
        }
        $this->addPresentUserAgrigateRole();
    }

    /**
     * add all resources used by the application to the access control list parent object
     * 
     * @param array $resources array of (string) resources 
     */
    protected function defineResources($resources)
    {
        foreach ($resources as $resource) {
            $this->addResource($resource);
        }
    }

    /**
     * Set user role and this ACL for navigation
     */
    protected function joinAclToNavigation()
    {
        Navigation::setDefaultAcl($this);
        Navigation::setDefaultRole('present_unique_user');
    }

    /**
     * Check whether role and parent role has been added to this ACL and store 
     * role so that it is not added a second time
     * 
     * @param AclUser\Entity\Role  $role
     */
    protected function checkAddParentRole($role)
    {
        $parent = $role->getParent();
        if ($parent) {
            $this->checkAddParentRole($parent);
            if (!$this->hasRole($role->getName())) {
                $this->addMemRole($role, $parent->getName());
            } else {
                
            }
        } else {
            if (!$this->hasRole($role->getName())) {
                $this->addMemRole($role);
            }
        }
    }

    /**
     * Get the User entity object that represents the present (logged in) user
     * 
     * @return null|User the logged in user
     */
    public function getPresentUser()
    {
        $user = null;
        if (null !== ($identity = $this->getPresentUserId())) {
            $user = $this->userManager->getUserById($identity);
        }
        return $user;
    }

    /**
     * Get the user id (auth service identity) the present (logged in) user
     * 
     * @return integer|null the present user id
     */
    public function getPresentUserId()
    {
        $identity = null;
        if ($this->authService->hasIdentity()) {
            $identity = $this->authService->getIdentity();
        }
        return $identity;
    }

    /**
     * Get the logged in user's e-mail address
     * 
     * @return string the logged in user's email address or "Identity" if user is not found
     */
    public function getPresentUserEmailAddress()
    {
        $result = 'Identity';
        $user = $this->getPresentUser();
        if ($user) {
            $result = $user->getEmail();
        }
        return $result;
    }

    /**
     * add the user role 'present_unique_user' that is used for all users 
     * And add the present users roles array as parent of this role
     */
    protected function addPresentUserAgrigateRole()
    {
        if (!$this->authService->hasIdentity()) {
            $this->addRole('present_unique_user', 'guest');
        } else {
            $userId = $this->authService->getIdentity();
            $user = $this->userManager->getUserById($userId);
            if ($user) {
                $this->setAgragateRolesByUserId($user);
            } else {
                $this->authService->clearIdentity();
                $this->addRole('present_unique_user', 'guest');
            }
        }
    }

    /**
     * Concatenate all users roles and add present_unique_user as child of all those roles
     * 
     * @param Entity\User $user
     */
    protected function setAgragateRolesByUserId($user)
    {
        $maps = $user->getRoleMaps();
        $userRoles = [];
        if (count($maps) == 0) {
            $userRoles[] = 'guest';
        }
        foreach ($maps as $map) {
            $role = $map->getRole();
            if (array_key_exists($role->getId(), $this->roleMemory) && !in_array($role->getName(), $userRoles)) {
                $userRoles[] = $this->roleMemory[$role->getId()];
            }
        }
        $this->addRole('present_unique_user', $userRoles);
    }

    /**
     * Persist roles for later use within this class
     * 
     * @param AclUser\Entity\Role $role
     * @param string|null $parentId
     */
    protected function addMemRole($role, $parentId = NULL)
    {
        $this->addRole($role->getName(), $parentId);
        $this->roleMemory[$role->getId()] = $role->getName();
    }

    /**
     * Based on ListenerAggregateInterface this method is called when application dispatches
     * 
     * @param MvcEvent $event
     * @return null
     */
    public function onDispatch(MvcEvent $event)
    {

        $routeMatch = $event->getRouteMatch();
        $resource = $routeMatch->getParam('controller');
        $privilege = $routeMatch->getParam('action');
        $allowed = $this->userIsAllowed($resource, $privilege);

        if ($allowed) {
            return;
        }
        return $this->disallowedRouteRedirect($event, $routeMatch);
    }

    /**
     * Handle request if user is not allowed the access the the requested resource and privilege
     * 
     * @param MvcEvent $event
     * @param RouteMatch $routeMatch
     * @return type
     */
    public function disallowedRouteRedirect(MvcEvent $event, RouteMatch $routeMatch)
    {
        $controller = $event->getTarget();
        switch (true) {
            case (!$this->checkThatRouteExists($routeMatch)):
                /* route does not exist */
                $controller->plugin('FlashMessenger')->setNamespace('error')->addMessage('The page that you requested does not exist.');
                return $controller->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
            case $this->authService->hasIdentity() && $this->isParticularRoute($routeMatch, 'user-auth', 'login'):
                /* route exists but user is not permitted */
                $controller->plugin('FlashMessenger')->setNamespace('error')->addMessage('You tried to log in but you are already logged in.');
                return $controller->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
            case $this->authService->hasIdentity():
                /* route exists but user is not permitted */
                $controller->plugin('FlashMessenger')->setNamespace('error')->addMessage('You do not have permission to visit the requested page.');
                return $controller->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
            case (!$this->isParticularRoute($routeMatch, 'user-auth', 'login') && $this->userIsAllowed('user-auth', 'login')):
                if ($this->isParticularRoute($routeMatch, 'user-auth', 'logout')) {
                    /* in case user who is not logged in tries to log out */
                    $controller->plugin('FlashMessenger')->setNamespace('error')->addMessage('You tried to log out but you are not logged in.');
                    return $controller->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'login']);
                }
                /* route exists - user is not logged in so redirect to login with redirect url so that they can maybe login and then access  */
                $uri = $event->getApplication()->getRequest()->getUri();
                // Make the URL relative (remove scheme, user info, host name and port)
                // to avoid redirecting to other domain by a malicious user.
                $uri->setScheme(null)->setHost(null)->setPort(null)->setUserInfo(null);
                $controller->plugin('FlashMessenger')->setNamespace('error')->addMessage('You do not have permission to visit the requested page.');
                return $controller->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'login'], ['query' => ['redirectUrl' => $uri->toString()]]);
            case (!$this->isParticularRoute($routeMatch, 'user-auth', 'logout') && $this->userIsAllowed('user-auth', 'logout')):
                return $controller->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'logout']);
            default:
                $controller->plugin('FlashMessenger')->setNamespace('error')->addMessage('Something went wrong.');
                return $controller->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
        }
    }

    /**
     * Check whether this route corresponds to a real controller and action
     * 
     * @param RouteMatch $routeMatch
     */
    private function checkThatRouteExists(RouteMatch $routeMatch)
    {
        $controller = $routeMatch->getParam('controller');
        if (!$this->hasResource($controller)) {
            //note that this cannot fail because The requested controller could not be mapped to an existing controller class. would have been called
        }
        $action = $routeMatch->getParam('action');
        foreach ($this->allRoles as $role) {
            if ($this->isAllowed($role, $controller, $action)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check whether this is particular route as defined by the controller and the action
     * 
     * @param RouteMatch $routeMatch
     * @param string $controller
     * @param string $action
     * @return boolean
     */
    private function isParticularRoute(RouteMatch $routeMatch, $controller, $action)
    {
        $isController = $routeMatch->getParam('controller') == $controller;
        $isAction = $routeMatch->getParam('action') == $action;
        return $isController && $isAction;
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
        return $this->isAllowed('present_unique_user', $resource, $privilege);
    }

}
