<?php

/**
 * Class UserManagerMockBuilder 
 *
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mock;

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
class UserManagerMockBuilder extends MockBuilder
{

    /**
     * Mock of \AclUser\Service\UserManager
     * 
     * @var Mock_Usermanager_**** 
     */
    protected $userManagerMock;

    /**
     * Initialise user manager mock
     */
    public function initialiseUserManagerMock()
    {
        $this->userManagerMock = $this->test->getMockBuilder(\AclUser\Service\UserManager::class)->disableOriginalConstructor()->getMock();
        $this->userManagerMock->expects($this->test->any())->method('fetchAllRoles')->willReturn($this->roleEntities);
        $this->setService(\AclUser\Service\UserManager::class,  $this->userManagerMock);
    }

    /**
     * Set up application for "basic" authorised user
     */
    public function basicAuthorised()
    {
        $this->setupUserManagerMockUser(true)->setupServiceAuthMock(true)
                ->finaliseAcl();
        return $this;
    }

    /**
     * Set up application for "user-manager" authorised user
     * 
     * @return type
     */
    public function userManagerAuthorised()
    {
        return $this->specificAuthorised('user-manager')->finaliseAcl();
    }

    /**
     * Set up application for unauthorised user (guest)
     * 
     * @return $this
     */
    public function unAuthorised()
    {
        $this->setupUserManagerMockUser(false)->setupServiceAuthMock(false)
                ->finaliseAcl();
        return $this;
    }

    /**
     * Call all functions to setup user with specific role
     * 
     * @param string $roleName the name of the role to be given to the mocked user
     * @return $this
     */
    public function specificAuthorised($roleName)
    {
        $this->setupUserManagerMockUser(true, $roleName)->setupServiceAuthMock(true);
        return $this;
    }

    /**
     * Initialises userManagerMock and mocks method getUserById
     * 
     * @param boolean $withUser whether a user is required
     * @param string $roleName the name of the role to be given to the mocked user
     * @param string $password the mocked user's password
     * @return $this
     */
    public function setupUserManagerMockUser($withUser = true, $roleName = 'basic', $password = 'shortpassword')
    {
        $this->setupFetchAllRoles();
        $user = $withUser ? $this->getNewUser($roleName, 'email@mailserver.com', $password) : null;
        $this->userManagerMock->expects($this->test->any())->method('getUserById')->willReturn($user);
        $this->userManagerMock->expects($this->test->any())->method('getAllLocales')->willReturn($this->getAllLocales());
        return $this;
    }

    /**
     * Tell userManagerMock to return value for getUserPhotoLocationById method
     * 
     * @return $this
     */
    public function setupUserManagerServePhoto()
    {
        $this->userManagerMock->expects($this->test->any())->method('getUserPhotoLocationById')->willReturn('./data/media/user-images/avatar.png');
        return $this;
    }

    /**
     * Mocks method validatePhotoUploadForm of userManagerMock
     * 
     * @return $this
     */
    public function setupValidatePhotoUpload()
    {
        $this->userManagerMock->expects($this->test->any())->method('validatePhotoUploadForm')->willReturn(['success' => '', 'errors' => []]);
        return $this;
    }

    /**
     * Mocks method $method of userManagerMock
     * 
     * @param string $method the method to be mocked
     * @param \Zend\Authentication\Result $result
     * @return $this
     */
    public function setupUserManagerReturnsResult($method, \Zend\Authentication\Result $result)
    {
        return $this->setupUserManagerReturnsAnything($method, $result);
    }

    /**
     * Mocks method $method of userManagerMock
     * 
     * @param string $method the method to be mocked
     * @param mixed $result
     * @return $this
     */
    public function setupUserManagerReturnsAnything($method, $result)
    {
        $this->userManagerMock->expects($this->test->any())->method($method)->willReturn($result);
        return $this;
    }

    /**
     * Mocks method getTranslatedErrorMesssages of userManagerMock
     * 
     * @return $this
     */
    public function setupGetTranslatedErrorMessages()
    {
        $this->userManagerMock->expects($this->test->any())->method('getTranslatedErrorMesssages')->willReturn([]);
        return $this;
    }

    /**
     * Mocks method getTranslatedErrorMesssages of userManagerMock so that system has all roles
     * 
     * @todo possibly update to use an XML file instead of the array
     */
    protected function setupFetchAllRoles()
    {
        $this->userManagerMock->expects($this->test->any())->method('fetchAllRoles')->willReturn($this->roleEntities);
    }

    /**
     * Get array of all locales
     * 
     * @return array
     */
    protected function getAllLocales()
    {
        return [
            'en_GB',
            'nl_NL',
            'es_ES',
            'fr_FR',
            'de_DE',
            'it_IT',
            'el_GR',
            'nn_NO',
            'pl_PL',
            'pt_PT',
            'ru_RU',
            'sv_SE',
        ];
    }

}
