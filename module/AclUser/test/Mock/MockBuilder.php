<?php

/**
 * Class MockBuilder 
 *
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mock;

use Zend\Stdlib\ArrayUtils;
use Zend\Mvc\Controller\AbstractActionController;
use AclUserTest\Mock\FlashMessengerTestCaseHelper;
use Zend\Mvc\MvcEvent;

require_once './module/Translate/test/Mock/OverrideFunctions.php';

/**
 * Contains logic for mocking AclUser\Service\UserManager and 
 * Zend\Authentication\AuthenticationService (as required) and removes and 
 * re-attaches  AccessControlList onDispatch listener
 * 
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class MockBuilder
{

    /**
     * Mock of Doctrine\ORM\EntityManager::class
     * 
     * @var Mock_Entitymanager_*** 
     */
    protected $entityManagerMock;

    /**
     * Mock of Doctrine\ORM\EntityRepository::class
     * 
     * @var Mock_Entityrepository_***
     */
    public $repoFinder;

    /**
     * Array of role entities
     * 
     * @var array 
     */
    protected $roleEntities = [];

    /**
     * Associative array of role entities with the name of the role as array key
     * 
     * @var array 
     */
    protected $allRoles = [];

    /**
     * The test where the MockBuilder was created
     * 
     * @var \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase 
     */
    protected $test;

    /**
     * Constructor
     * allows overwrite of factories and services in the service manager
     * 
     * @param \Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase $test
     * @return $this
     */
    public function __construct($test)
    {
        $test->getApplicationServiceLocator()->setAllowOverride(true);
        $this->test = $test;
        $this->populateAllRoles();
        return $this;
    }

    /**
     * 
     * @return FlashMessengerTestCaseHelper
     */
    public function createFlashMessengerTestCaseHelper()
    {
        return new FlashMessengerTestCaseHelper($this->test);
    }

    /**
     * Get test configuration
     * 
     * @param boolean $useOverrides whether to hack native functions 
     * @see ./module/Translate/test/Mock/OverrideFunctions.php
     * @return array
     */
    public static function getConfig($useOverrides = false)
    {
        $useOverrides ? $GLOBALS['use_override'] = true : null;
        $configOverrides = ['module_listener_options' => ['config_glob_paths' => [
                    realpath(__DIR__ . '/../../../../config/autoload/global-test.php'),
        ]]];
        return ArrayUtils::merge(
                        include __DIR__ . '/../../../../config/application.config.php', $configOverrides
        );
    }

    /**
     * initialise Doctrine entity manager mock
     * 
     * @return $this
     */
    public function initialiseEntityManagerMock()
    {
        $this->entityManagerMock = $this->test->getMockBuilder(\Doctrine\ORM\EntityManager::class)->disableOriginalConstructor()->getMock();
        $this->entityManagerMock->expects($this->test->any())->method('persist')->willReturn(null);
        $this->entityManagerMock->expects($this->test->any())->method('merge')->willReturn(null);
        $this->entityManagerMock->expects($this->test->any())->method('flush')->willReturn(null);
        $this->entityManagerMock->expects($this->test->any())->method('remove')->willReturn(null);

        $this->repoFinder = $this->test->getMockBuilder(\Doctrine\ORM\EntityRepository::class)->disableOriginalConstructor()->getMock();
        $this->setService('doctrine.entitymanager.orm_default', $this->entityManagerMock);
        return $this;
    }

    /**
     * 
     * Setup user
     * 
     * @param boolean $returnUser whether to return user or null
     * @param boolean $mockClass whether to specify class
     * @param array $moreMethods array of methods and values to pass to user REMEMBER this is a User entity object so set do not mock
     * @param false|integer $ifAt
     * @return mocked user
     */
    public function setupUser($returnUser = true, $mockClass = true, $moreMethods = [], $ifAt = false)
    {
        $user = null;
        $class = $mockClass ? \AclUser\Entity\User::class : false;
        $this->setEntityManagerExpectsGetRepository($class);
        if ($returnUser) {
            $user = $this->getNewUser();
            foreach ($moreMethods as $method => $value) {
                $user->$method($value);
            }
        }
        if ($ifAt !== false) {
            $this->repoFinder->expects($this->test->at($ifAt))->method('findOneBy')->willReturn($user);
        } else {
            $this->repoFinder->expects($this->test->any())->method('findOneBy')->willReturn($user);
        }
        return $user;
    }

    /**
     * Mock repository finder expects method
     * 
     * @param string $type 
     * @param false|integer $ifAt
     */
    public function mockOtherRepoFinder($type = 'role', $ifAt = false)
    {
        $entity = null;
        switch ($type) {
            case 'role':
                $entity = $this->getRoleByName('basic');
                break;
        }
        if ($ifAt !== false) {
            $this->repoFinder->expects($this->test->at($ifAt))->method('findOneBy')->willReturn($entity);
        }
    }

    /**
     * Tell Doctrine entity manager mock what to return when getRepository is called
     * 
     * @param string|false $class 
     * @return $this
     */
    public function setEntityManagerExpectsGetRepository($class = false)
    {
        if (false !== $class) {
            $this->entityManagerMock->expects($this->test->any())->method('getRepository')->with($this->test->equalTo($class))->willReturn($this->repoFinder);
        } else {
            $this->entityManagerMock->expects($this->test->any())->method('getRepository')->willReturn($this->repoFinder);
        }
        return $this;
    }

    /**
     * Tell application ServiceManager which service to return for $serviceName
     * 
     * @param string $serviceName the class or alias to return
     * @param object $service the service for ServiceManager to return for ServiceManager::get
     */
    public function setService($serviceName, $service)
    {
        $this->test->getApplicationServiceLocator()->setService($serviceName, $service);
    }

    /**
     * Get service from application ServiceManager
     * 
     * @param string $serviceName class or alias of service required
     */
    public function getService($serviceName)
    {
        return $this->test->getApplicationServiceLocator()->get($serviceName);
    }

    /**
     * Build (new) service from application ServiceManager

     * @param string $serviceName class or alias of service required
     */
    public function buildService($serviceName)
    {
        return $this->test->getApplicationServiceLocator()->build($serviceName);
    }

    /**
     * Removes and re-attaches  AccessControlList onDispatch listener
     * 
     * @return $this
     */
    public function finaliseAcl()
    {
        $sharedEventManager = $this->test->getApplication()->getEventManager()->getSharedManager();
        // Register the event listener method.  
        $acl = $this->buildService('unmockedAccessControlList');
        $this->setService('AccessControlList', $acl);
        $sharedEventManager->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [$acl, 'onDispatch'], 102);
        return $this;
    }

    /**
     * Initialise $this->roleEntities and $this->allRoles for later use
     */
    protected function populateAllRoles()
    {
        $roles = ['basic' => null, 'base-translate' => 1, 'site-language-admin' => 2, 'uber-translator' => 2, 'user-manager' => 2, 'dutch-translator' => 2];
        $this->roleEntities = [];
        $this->allRoles = [];
        $index = 0;
        foreach ($roles as $role => $parent) {
            $index++;
            $entity = new \AclUser\Entity\Role();
            $entity->setId($index);
            $entity->setName($role);
            $entity->setDescription('description ');
            $entity->setParent(null === $parent ? null : $this->roleEntities[$parent - 1]);
            $this->roleEntities[] = $entity;
            $this->allRoles[$role] = $entity;
        }
    }

    /**
     * Get array of role entities created by populateAllRoles
     * 
     * @return array of role entities
     */
    public function getAllRoles()
    {
        return $this->roleEntities;
    }

    /**
     * Get Role object entity by name
     * 
     * @param string $name name of role 
     * @return Role object entity
     */
    public function getRoleByName($name)
    {
        if (array_key_exists($name, $this->allRoles)) {
            return $this->allRoles[$name];
        }
    }

    /**
     * Create logged in user and assign one role to them
     * 
     * @param string $roleName the name of the role to be given to the mocked user
     * @param string $email
     * @param string $password
     * @return \AclUser\Entity\User
     */
    public function getNewUser($roleName = 'basic', $email = 'email@mailserver.com', $password = '$2y$10$nuG0x78kdCEDBNt.AT5iPuR.uXJl8tu.j955rwxA4VK9t4HKSevvW')
    {
        $user = new \AclUser\Entity\User;
        $user->setId(1);
        $user->setFullName('Peter Parker');
        $user->setEmail($email);
        $user->setPhoto(false);
        $user->setStatus(true);
        $user->setPassword($password);
        $user->setDateCreated(new \DateTime());
        if (array_key_exists($roleName, $this->allRoles)) {
            $roleMap = new \AclUser\Entity\UserRoleMap();
            $roleMap->setUser($user);
            $roleEntity = $this->allRoles[$roleName];
            $roleMap->setRole($roleEntity);
            $roleMaps = new \Doctrine\Common\Collections\ArrayCollection([$roleMap]);
            $user->setRoleMaps($roleMaps);
        }
        return $user;
    }

    /**
     * Mock Zend\Authentication\AuthenticationService with methods hasIdentity & getIdentity
     * 
     * @param boolean $hasIdentity whether user is logged in defaults to false
     * @param integer $identity id of user if they are logged in defaults to 1
     * @return $this
     */
    public function setupServiceAuthMock($hasIdentity = false, $identity = 1)
    {
        $mockAuth = $this->test->getMockBuilder(\Zend\Authentication\AuthenticationService::class)->disableOriginalConstructor()->getMock();
        $mockAuth->expects($this->test->any())->method('hasIdentity')->willReturn($hasIdentity);
        $mockAuth->expects($this->test->any())->method('getIdentity')->willReturn($identity);
        $mockAuth->expects($this->test->any())->method('clearIdentity')->willReturn(null);
        $this->setService(\Zend\Authentication\AuthenticationService::class, $mockAuth);
        return $this;
    }

    /**
     * Get Mock of any class or alias and set the methods to be mocked
     * 
     * @param string $class class or alias of service
     * @param array $methods array of methods with elements in form 'method name ' => 'return value '
     * @return mocked service
     */
    public function getMocked($class, $methods)
    {
        $mock = $this->test->getMockBuilder($class)->disableOriginalConstructor()->getMock();
        foreach ($methods as $method => $returnValue) {
            $mock->expects($this->test->any())->method($method)->willReturn($returnValue);
        }
        return $mock;
    }

    /**
     * Mock Zend\Authentication\AuthenticationService
     * 
     * @param array $methods array of methods with elements in form 'method name ' => 'return value '
     */
    public function mockAuthService($methods = [])
    {
        $authService = $this->test->getMockBuilder(\Zend\Authentication\AuthenticationService::class)->disableOriginalConstructor()->getMock();
        foreach ($methods as $method => $returnValue) {
            $authService->expects($this->test->any())->method($method)->willReturn($returnValue);
        }
        $this->setService(\Zend\Authentication\AuthenticationService::class, $authService);
    }

}
