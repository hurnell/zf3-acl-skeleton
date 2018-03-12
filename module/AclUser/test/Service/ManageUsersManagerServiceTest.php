<?php

/**
 * Class ManageUsersManagerServiceTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Service;

use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;

/**
 * Test various aspects of AclUser\Service\AuthManager
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class ManageUsersManagerServiceTest extends AbstractHttpControllerTestCase
{

    protected $builder;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ServiceMockBuilder::getConfig());
        parent::setUp();
        $this->builder = new ServiceMockBuilder($this);
        $this->builder->initialiseEntityManagerMock();
    }

    /**
     * 
     * @param boolean $mockMailViewMessage whether to mock AclUser\Mail\MailMessage before creation
     * @return \AclUser\Service\UserManager
     */
    protected function getManagerService()
    {
        return $this->builder->buildServiceWithOptionalSessionManager(\AclUser\Service\ManageUsersManager::class);
    }

    public function testManagerControllerCanGetAllUsers()
    {

        $this->builder->setupRepoFinderMethod(['findBy' => []]);
        $service = $this->getManagerService();
        $result = $service->getAllUsers();
        $this->assertTrue($result == [], 'getAllUsers should (in this case) return []');
    }

    public function testManagerControllerCanGetUser()
    {
        $this->builder->setupUser();
        $service = $this->getManagerService();
        $user = $service->findUserById(1);
        $this->assertTrue($user->getFullName() == 'Peter Parker', 'user full name should be "Peter Parker"');
    }

    public function testManagerControllerCanGetNullUser()
    {
        $this->builder->setupUser(false);
        $service = $this->getManagerService();
        $user = $service->findUserById(1);
        $this->assertTrue($user == null, 'should return null for user');
    }

    public function testManagerControllerCanGetRole()
    {
        $this->builder->setupRole(true, 'basic');
        $service = $this->getManagerService();
        $role = $service->findRoleById(1);
        $this->assertTrue($role->getName() == 'basic', 'role name should be "basic"');
    }

    public function testManagerControllerCanGetNullRole()
    {
        $this->builder->setupRole(false);
        $service = $this->getManagerService();
        $role = $service->findRoleById(1);
        $this->assertTrue($role == null, 'should return null');
    }

    public function testManagerControllerCanGetRolesByUser()
    {

        $user = $this->builder->getNewUser();
        $allRoles = $this->builder->getAllRoles();
        $this->builder->setupRepoFinderMethod(['findBy' => $allRoles]);
        $service = $this->getManagerService();
        $result = $service->getRolesByUser($user);
        $this->assertTrue(is_array($result), 'getRolesByUser should return array');
        $this->assertTrue($result[0] instanceof \Doctrine\Common\Collections\ArrayCollection, 'getRolesByUser should return ArrayCollection as first element of resultant array');
        $this->assertTrue(is_array($result[1]), 'getRolesByUser should return array as secon element of resultant array');
    }

    public function testManagerControllerCanUpdateUserRoleMembershipAdd()
    {
        $methods = [
            'findOneBy' => [
                'index' => 0,
                'value' => $this->builder->getNewUser()
            ],
            'findOneBy' => [
                'index' => 1,
                'value' => $this->builder->getRoleByName('basic')
            ],
        ];
        $this->builder->setupRepoFinderMethodWithParams($methods);
        $service = $this->getManagerService();
        $result = $service->updateUserRoleMembership('add', 1, 2);
        $this->assertTrue(true);
    }

    public function testManagerControllerCanUpdateUserRoleMembershipRemove()
    {
        $methods = [
            'findOneBy' => [
                'index' => 0,
                'value' => $this->builder->getNewUser()
            ],
            'findOneBy' => [
                'index' => 1,
                'value' => $this->builder->getRoleByName('basic')
            ],
            'findOneBy' => [
                'index' => 2,
                'value' => $this->builder->getRoleByName('basic')
            ],
        ];
        $this->builder->setupRepoFinderMethodWithParams($methods);
        $service = $this->getManagerService();
        $result = $service->updateUserRoleMembership('remove', 1, 2);
        $this->assertTrue(true);
    }

    public function testManagerControllerCanToggleUserStatus()
    {
        $initialUser = $this->builder->setupUser(true, true, ['setStatus' => false]);
        $this->assertTrue($initialUser->getStatus() == false, 'status of user should initially be false');
        $service = $this->getManagerService();
        $true = $service->toggleSuspensionUserById(1);
        $this->assertTrue($initialUser->getStatus() == true, 'status of user should have been toggled to true');
        $this->assertTrue($true, 'toggleSuspensionUserById should return true');
    }

    public function testManagerControllerCanToggleUserStatusInitiallyTrue()
    {
        $initialUser = $this->builder->setupUser(true, true, ['setStatus' => true]);
        $this->assertTrue($initialUser->getStatus() == true, 'status of user should initially be true');
        $service = $this->getManagerService();
        $true = $service->toggleSuspensionUserById(1);
        $this->assertTrue($initialUser->getStatus() == false, 'status of user should have been toggled to false');
        $this->assertTrue($true, 'toggleSuspensionUserById should return true');
    }

    public function testManagerControllerCanDeleteUserById()
    {
        $this->builder->setupUser();
        $service = $this->getManagerService();
        $null = $service->deleteUserById(1);
        $this->assertNull($null , 'deleteUserById should return null');
    }

}
