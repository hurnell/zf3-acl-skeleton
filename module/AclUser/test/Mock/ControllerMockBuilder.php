<?php

/**
 * Class ControllerMockBuilder 
 *
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mock;

use AclUserTest\Mock\UserManagerMockBuilder;

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
class ControllerMockBuilder extends UserManagerMockBuilder
{

    /**
     * Mock of \AclUser\Service\ManageUsersManager
     * 
     * @var Mock_Usermanager_**** 
     */
    protected $manageUsersManagerMock;

    /**
     * Mock of \Translate\Service\TranslationManager
     * 
     * @var Mock_Translationmanager_**** 
     */
    protected $translationManagerMock;

    /**
     * Mock of \AclUser\Service\AuthManager
     * 
     * @var Mock_Authmanager_**** 
     */
    protected $authManagerMock;

    /**
     * Constructor
     * 
     * @param type $test
     */
    public function __construct($test)
    {
        \Application\Log\Log::getInstance()->info(__METHOD__ . 'one');
        parent::__construct($test);
        \Application\Log\Log::getInstance()->info(__METHOD__ . 'two');
        $this->initialiseAuthManagerMock();
        \Application\Log\Log::getInstance()->info(__METHOD__ . 'three');
        $this->initialiseUserManagerMock();
        \Application\Log\Log::getInstance()->info(__METHOD__ . 'four');
    }

    /**
     * Initialise ManageUsersManagerMock
     * 
     * @return $this
     */
    public function initialiseManageUsersManagerMock()
    {
        $this->manageUsersManagerMock = $this->test->getMockBuilder(\AclUser\Service\ManageUsersManager::class)->disableOriginalConstructor()->getMock();
        $this->setService(\AclUser\Service\ManageUsersManager::class, $this->manageUsersManagerMock);
        return $this;
    }

    /**
     * Initialise authManagerMock
     * 
     * @return $this
     */
    public function initialiseAuthManagerMock()
    {
        $this->authManagerMock = $this->test->getMockBuilder(\AclUser\Service\AuthManager::class)->disableOriginalConstructor()->getMock();
        $this->setService(\AclUser\Service\AuthManager::class, $this->authManagerMock);
        return $this;
    }

    /**
     * Initialise translationManagerMock
     * 
     * @return $this
     */
    public function initialiseTranslationManagerMock()
    {
        $this->translationManagerMock = $this->test->getMockBuilder(\Translate\Service\TranslationManager::class)->disableOriginalConstructor()->getMock();
        $this->setService(\Translate\Service\TranslationManager::class, $this->translationManagerMock);
        return $this;
    }

    /**
     * Mocks method $method of authManagerMock
     * 
     * @param string $method the method to be mocked
     * @param mixed $result
     * @return $this
     */
    public function setupAuthManagerReturnsAnything($method, $result)
    {
        $this->authManagerMock->expects($this->test->any())->method($method)->willReturn($result);
        return $this;
    }

    /**
     * Tell manageUsersManagerMock to return $result for mocked method $method
     * 
     * @param string $method the method to be mocked
     * @param mixed $result what the mocked method should return
     * @return $this
     */
    public function setupManageUsersManagerReturnsAnything($method, $result)
    {
        if (!isset($this->manageUsersManagerMock)) {
            $this->initialiseManageUsersManagerMock();
        }
        $this->manageUsersManagerMock->expects($this->test->any())->method($method)->willReturn($result);
        return $this;
    }

    /**
     * Mocks method $method of authManagerMock
     * 
     * @param string $method the method to be mocked
     * @param \Zend\Authentication\Result $result
     * @return $this
     */
    public function setupAuthManagerReturnsResult($method, \Zend\Authentication\Result $result)
    {
        return $this->setupAuthManagerReturnsAnything($method, $result);
    }

    /**
     * Get Parent Role by id
     * 
     * @param integer $parentRoleId the id of the parent role
     * @return \AclUser\Entity\Role
     */
    protected function getParentRole($parentRoleId)
    {
        switch ($parentRoleId) {
            case null:
                return null;
            case 1:
                $name = 'basic';
                $roleId = null;
                break;
            case 2:
                $name = 'base-translate';
                $roleId = 1;
                break;
        }
        $role = new \AclUser\Entity\Role();
        $role->setName($name);
        $role->setDescription('description');
        $role->setParent($this->getParentRole($roleId));
        return $role;
    }

    /**
     * Set up mocked Translate\Form\TranslationForm
     * 
     * @return $this
     */
    public function setupTranslationFormMock()
    {
        $mockForm = $this->test->getMockBuilder(\Translate\Form\TranslationForm::class)->disableOriginalConstructor()->getMock();
        $mockForm->expects($this->test->any())->method('isValid')->willReturn(true);

        $mockManager = $this->test->getMockBuilder(\Translate\Service\TranslationManager::class)->disableOriginalConstructor()->getMock();
        $mockManager->expects($this->test->any())->method('updateTranslation')->willReturn(null);
        $this->setService(\Translate\Service\TranslationManager::class, $mockManager);
        return $this;
    }

    /**
     * Tell translationManagerMock to return $returnValue for mocked method $method
     * 
     * @param string $method the method to be mocked
     * @param mixed $returnValue what the mocked method should return
     */
    public function setupTranslateManagerMethodMock($method, $returnValue)
    {
        if (!isset($this->translationManagerMock)) {
            $this->initialiseTranslationManagerMock();
        }
        $this->translationManagerMock->expects($this->test->any())->method($method)->willReturn($returnValue);
    }

    /**
     * Get array of enabled social providers from Social\Options\ModuleOptions
     * 
     * @return array
     */
    public function getEnabledSocialProviders()
    {
        $moduleOptions = $this->getService(\Social\Options\ModuleOptions::class);
        return $moduleOptions->getEnabledProviders();
    }

    /**
     * Mock session container 
     * 
     * @param array $returns associative array (integer keys) of $return values 
     * @return Session container mock
     */
    public function mockSessionContainer($returns = [])
    {
        $mock = $this->test->getMockBuilder(\Zend\Session\Container::class)->disableOriginalConstructor()->getMock();
        foreach ($returns as $index => $return) {
            $mock->expects($this->test->at($index))->method('__get')->willReturn($return);
        }
        $this->setService('social_saved_state', $mock);
        return $mock;
    }

    /**
     * Set mocked Social\Service\SocialManager
     * 
     * @param string $providerName
     * @param mixed $sessionContainer
     * @param boolean $getClient
     */
    public function mockClient($providerName, $sessionContainer, $getClient = true)
    {
        $methods = ['startProvider'];
        if ($getClient) {
            $methods[] = 'getClient';
        }
        $moduleOptions = $this->getService(\Social\Options\ModuleOptions::class);
        $socialAuthManager = $this->getService(\Social\Service\SocialAuthManager::class);
        $constructorArgs = [$moduleOptions, $socialAuthManager, $sessionContainer];

        $socialManagerMock = $this->test->getMockBuilder(\Social\Service\SocialManager::class)->setMethods($methods)->setConstructorArgs($constructorArgs)->getMock();
        $parts = explode('_', $providerName);
        $class = '\Social\Providers\\';
        foreach ($parts as $part) {
            $class .= ucfirst($part);
        }
        $class .= 'Provider';
        if ($getClient) {
            $provider = $this->test->getMockBuilder($class)->disableOriginalConstructor()->getMock();
            $provider->expects($this->test->any())->method('sendClientRequest')->willReturn(['email' => 'email', 'name' => 'name', 'provider' => $providerName]);
            $socialManagerMock->expects($this->test->any())->method('getClient')->willReturn(null);
        } else {
            $provider = $this->test->getMockBuilder($class)->setMethods(['checkReturnedQuery'])->setConstructorArgs([$socialManagerMock])->getMock();
        }
        $socialManagerMock->expects($this->test->any())->method('startProvider')->willReturn($provider);
        $this->setService(\Social\Service\SocialManager::class, $socialManagerMock);
    }

    /**
     * Get mock of Social\Service\SocialManager::class
     * 
     * @return Mock of Social\Service\SocialManager
     */
    public function getSocialManagerClientMock()
    {
        $sessionContainer = $this->getService('social_saved_state');
        $moduleOptions = $this->getService(\Social\Options\ModuleOptions::class);
        $socialAuthManager = $this->getService(\Social\Service\SocialAuthManager::class);
        $constructorArgs = [$moduleOptions, $socialAuthManager, $sessionContainer];

        $socialManagerMock = $this->test->getMockBuilder(\Social\Service\SocialManager::class)->setMethods(['getClient'])->setConstructorArgs($constructorArgs)->getMock();

        return $socialManagerMock;
    }

    /**
     * Get URL that social provider should be redirected to after authentication
     * 
     * @param type $providerName
     * @return string URL that social provider should be redirected to after authentication
     */
    public function getCallbackUrl($providerName)
    {
        return 'http://www.example.com/en_GB/social/redirected/' . $providerName;
    }

}
