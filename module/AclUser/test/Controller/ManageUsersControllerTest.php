<?php

/**
 * Class AuthControllerTest 
 *
 * @package     AclUserTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUserTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ControllerMockBuilder;

/**
 * Test various aspects of AclUser\Controller\AuthController
 */
class ManageUsersControllerTest extends AbstractHttpControllerTestCase
{

    /**
     * FlashMessengerTestCaseHelper is created in MockBuilder
     * 
     * @see \AclUserTest\Mock\MockBuilder::createFlashMessengerTestCaseHelper
     * @var \AclUserTest\Mock\FlashMessengerTestCaseHelper 
     */
    protected $fmtc;

    /**
     * Instance of class that is used to mock AclUser\Service\UserManager,
     * Zend\Authentication\AuthenticationService and replace AccessControlList 
     * 
     * @var ControllerMockBuilder 
     */
    protected $builder;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ControllerMockBuilder::getConfig());
        parent::setUp();
        $this->builder = new ControllerMockBuilder($this);
        $this->fmtc = $this->builder->createFlashMessengerTestCaseHelper();
    }

    public function testUserManagerCanAccessListUserPage()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('getAllUsers', []);

        $this->dispatch('/en_GB/manage-users/list-users', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('list-users');
    }

    public function testUserManagerIsRedirectedFromManageUserRolesNoUser()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('findUserById', null);

        $this->dispatch('/en_GB/manage-users/manage-user-roles/1', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('manage-user-roles');
        $this->assertRedirectRegex('/\/manage-users\/list-users/');
    }

    public function testUserManagerCanAccessManageUserRoles()
    {
        $roleMaps = $this->builder->getNewUser()->getRoleMaps();
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('findUserById', $this->builder->getNewUser('user-manager'))
                ->setupManageUsersManagerReturnsAnything('getRolesByUser', [$roleMaps, []]);
        foreach ($roleMaps as $roleMap) {
            $roleMap->getId();
            $roleMap->getuser();
        }
        $this->dispatch('/en_GB/manage-users/manage-user-roles/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('manage-user-roles');
    }

    public function testUserManagerFailsForNonExistentRoute()
    {
        $this->builder->userManagerAuthorised();

        $this->dispatch('/en_GB/index/nowhere', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Application');
        $this->assertControllerName('index');
        $this->assertControllerClass('IndexController');
        $this->assertActionName('nowhere');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'The page that you requested does not exist.');
    }

    public function testEditProfileRedirectsWhenUserNotFound()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('findUserById', null);

        $this->dispatch('/en_GB/manage-users/edit-profile/1', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('edit-profile');
        $this->assertRedirectRegex('/\/manage-users\/list-users/');
    }

    public function testEditProfileRendersWhenUserIsFound()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('findUserById', $this->builder->getNewUser('user-manager'));

        $this->dispatch('/en_GB/manage-users/edit-profile/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('edit-profile');
    }

    public function testUserCanGetPhotoUploadForm()
    {
        $this->builder->userManagerAuthorised();

        $this->dispatch('/en_GB/manage-users/ajax-get-photo-upload-form/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-get-photo-upload-form');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('view', $json);
    }

    public function testUserCanDeleteuserById()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('deleteUserById', true);

        $this->dispatch('/en_GB/manage-users/ajax-delete-user-by-id/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-delete-user-by-id');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $json);
    }

    public function testUserCanToggleUserStatusById()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('toggleSuspensionUserById', true);

        $this->dispatch('/en_GB/manage-users/ajax-toggle-suspension-user-by-id/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-toggle-suspension-user-by-id');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $json);
    }

    public function testUserCanUpdateUserRoleMembershipById()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('updateUserRoleMembership', null);

        $this->dispatch('/en_GB/manage-users/ajax-update-user-role-membership/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-update-user-role-membership');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('return', $json);
        $this->assertTrue($json['return'] == 'nothing needed');
    }

    public function testUserReceiveUserPhotoFile()
    {
        $this->builder->userManagerAuthorised()
                ->setupManageUsersManagerReturnsAnything('validatePhotoUploadForm', ['success' => true, 'errors' => []])
                ->setupManageUsersManagerReturnsAnything('getTranslatedErrorMesssages', []);

        $this->dispatch('/en_GB/manage-users/ajax-receive-user-photo-file/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-receive-user-photo-file');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success']);
        $this->assertArrayHasKey('errors', $json);
        $this->assertTrue($json['errors'] === []);
    }

    public function testUnauthorisedUserCannotAccessAjaxGetBasicProfileFormAction()
    {

        $this->builder->unAuthorised();
        $this->dispatch('/en_GB/manage-users/ajax-get-basic-profile-form/1', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->assertRedirectRegex('/redirectUrl=/');
        $this->assertRedirectRegex('/\/manage-users\/ajax-get-basic-profile-form/');
    }

    public function testAjaxGetBasicProfileFormActionRedirectsIfUserNotFound()
    {
        $this->builder->initialiseEntityManagerMock();
        $this->builder->userManagerAuthorised();
        $this->builder->setupUser(false);
        $this->dispatch('/en_GB/manage-users/ajax-get-basic-profile-form/1', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertRedirectRegex('/\/manage-users\/list-users/');
    }

    public function testAjaxGetBasicProfileFormReturnResponseIfUserFound()
    {
        $this->builder->initialiseEntityManagerMock();
        $this->builder->userManagerAuthorised();
        $this->builder->setupUser();
        $this->dispatch('/en_GB/manage-users/ajax-get-basic-profile-form/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('view', $json);
        $this->assertArrayHasKey('success', $json);
        $this->assertEquals(false, $json['success']);
    }

    public function testAjaxGetBasicProfileFormReturnResponseIfUserFoundPostNotValid()
    {
        $this->builder->initialiseEntityManagerMock();
        $this->builder->userManagerAuthorised();
        $this->builder->setupUser();
        $this->dispatch('/en_GB/manage-users/ajax-get-basic-profile-form/1', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('manage-users');
        $this->assertControllerClass('ManageUsersController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('view', $json);
        $this->assertArrayHasKey('success', $json);
        $this->assertEquals(false, $json['success']);
    }

}
