<?php

/**
 * Class ServiceMockBuilder 
 *
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mock;

use AclUserTest\Mock\MockBuilder;
use AclUser\Mail\MailMessage;
use Zend\Mail\Transport\Smtp;
use org\bovigo\vfs\vfsStream;

/**
 * Contains logic for mocking Services
 * 
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class ServiceMockBuilder extends MockBuilder
{

    /**
     * Set up getRepository methods 
     * 
     * @param array $methods associative array of $method=>$returnValue
     */
    public function setupRepoFinderMethod($methods = [])
    {
        $this->setEntityManagerExpectsGetRepository(false);
        foreach ($methods as $method => $returnValue) {
            $this->repoFinder->expects($this->test->any())->method($method)->willReturn($returnValue);
        }
    }

    /**
     * Set up getRepository methods with index of call
     * 
     * @param array $methods associative array of $method=>$returnValue $returnValue in form 'index'=>'value'
     */
    public function setupRepoFinderMethodWithParams($methods = [])
    {
        $this->setEntityManagerExpectsGetRepository(false);
        foreach ($methods as $method => $returnValue) {
            $this->repoFinder->expects($this->test->at($returnValue['index']))->method($method)->willReturn($returnValue['value']);
        }
    }

    /**
     * Set up Role entity object
     * 
     * @param boolean $returnRole return role or null
     * @param string $name the name of the role
     * @param boolean $mockClass whether to pass class to getRepository
     * @return \AclUserTest\Mock\Role
     */
    public function setupRole($returnRole = true, $name = 'basic', $mockClass = true)
    {
        $role = null;
        $class = \AclUser\Entity\Role::class;
        $this->setEntityManagerExpectsGetRepository($mockClass ? $class : false);
        if ($returnRole) {
            $role = new $class();
            $role->setName($name);
        }
        $this->repoFinder->expects($this->test->any())->method('findOneBy')->willReturn($role);
        return $role;
    }

    /**
     * Set up UserRoleMap entity object
     * 
     * @param boolean $mockClass
     * @param array $returnArray
     */
    public function setupUserRoleMap($mockClass = true, $returnArray = [])
    {
        $class = \AclUser\Entity\UserRoleMap::class;
        $this->setEntityManagerExpectsGetRepository($mockClass ? $class : false);
        $this->repoFinder->expects($this->test->any())->method('findBy')->willReturn($returnArray);
    }

    /**
     * Build service with optional session manager
     * 
     * @param string $class to be built
     * @param boolen $withSessionManager whether to mock session manager
     * @return object class object that was built
     */
    public function buildServiceWithOptionalSessionManager($class, $withSessionManager = false)
    {
        if ($withSessionManager) {
            $this->getSessionManagerMock();
        }
        return $this->buildService($class);
    }

    /**
     * Mock Zend\Session\SessionManager and set as service for application 
     * 
     * @return Mock_Sessionmanager_***
     */
    public function getSessionManagerMock()
    {
        $mock = $this->getMocked(\Zend\Session\SessionManager::class, ['rememberMe' => null]);
        $this->setService(\Zend\Session\SessionManager::class, $mock);
        return $mock;
    }

    /**
     * Mock form given by class $class
     * 
     * @param string $class the class of the form to be mocked
     * @param boolean $isValid value $form->isValid() returns
     * @param array $data post data
     * @param array $otherMethods key value array of other methods
     * @return mock
     */
    public function mockForm($class, $isValid, $data = [], $otherMethods = [])
    {
        $form = $this->test->getMockBuilder($class)->disableOriginalConstructor()->getMock();
        $form->expects($this->test->any())->method('isValid')->willReturn($isValid);
        $form->expects($this->test->any())->method('get')->willReturn(new \Zend\Form\Element\Text('name'));
        $form->expects($this->test->any())->method('getData')->willReturn($data);

        foreach ($otherMethods as $method => $reurnValue) {
            $form->expects($this->test->any())->method($method)->willReturn($reurnValue);
        }
        return $form;
    }

    /**
     * Mock AclUser\Mail\MailMessage and set methods
     */
    public function mockMailViewMessage()
    {
        $mailViewMessageMock = $this->test->getMockBuilder(\AclUser\Mail\MailMessage::class)->disableOriginalConstructor()->getMock();
        $mailViewMessageMock->expects($this->test->any())->method('setTo')->willReturn($mailViewMessageMock);
        $mailViewMessageMock->expects($this->test->any())->method('setSubject')->willReturn($mailViewMessageMock);
        $mailViewMessageMock->expects($this->test->any())->method('setViewScript')->willReturn($mailViewMessageMock);
        $mailViewMessageMock->expects($this->test->any())->method('setViewParams')->willReturn($mailViewMessageMock);
        $mailViewMessageMock->expects($this->test->any())->method('embedImageFromSrc')->willReturn($mailViewMessageMock);
        $mailViewMessageMock->expects($this->test->any())->method('setLayoutTemplate')->willReturn($mailViewMessageMock);
        $mailViewMessageMock->expects($this->test->any())->method('sendEmailBasedOnViewScript')->willReturn(null);
        $this->setService('mailViewMessage', $mailViewMessageMock);
    }

    /**
     * Get array of Roles
     * 
     * @return array of Roles
     */
    public function getAllRoles()
    {
        return $this->roleEntities;
    }

    /**
     * Get MailMessage after passing mocked PhpRenderer
     * 
     * @param boolean $smtp
     * @param boolean $withUsername
     * @param boolean $withSender
     * @return MailMessage
     */
    public function getMailMessageService($smtp = true, $withUsername = true, $withSender = true)
    {
        defined('PUBLIC_PATH') || define('PUBLIC_PATH', null);
        $renderer = new \Zend\View\Renderer\PhpRenderer();
        $resolver = new \Zend\View\Resolver\TemplatePathStack();
        $resolver->addPath('./module/AclUser/test/Mail/email/');
        $renderer->setResolver($resolver);
        $mailMessage = new MailMessage($renderer);
        if ($smtp) {
            $transport = $this->test->getMockBuilder(Smtp::class)->disableOriginalConstructor()->getMock();
            $options = $this->test->getMockBuilder(\Zend\Mail\Transport\SmtpOptions::class)->disableOriginalConstructor()->getMock();
            $transport->expects($this->test->any())->method('getOptions')->willReturn($options);
            $transport->expects($this->test->any())->method('send')->willReturn(null);
            $smtpOptions = $this->getConnectionConfig($withUsername, $withSender);
            $options->expects($this->test->any())->method('getConnectionConfig')->willReturn($smtpOptions);
            $mailMessage->setSmtpTransport($transport);
        }
        return $mailMessage;
    }

    /**
     * Get connection configuration array
     * 
     * @param boolean $withUsername
     * @param boolean $withSender
     * @return array
     */
    protected function getConnectionConfig($withUsername = true, $withSender = true)
    {
        $connectionConfig = [
            'password' => 'password',
            'ssl' => 'tls',
        ];
        if ($withUsername) {
            $connectionConfig['sender'] = 'Application Administrator';
        }
        if ($withSender) {
            $connectionConfig['username'] = 'admin@mailserver.com';
        }
        return $connectionConfig;
    }

    /**
     * Get fake vfsStream directory structure
     * 
     * @return array
     */
    public function getFakeDirectoryStructure()
    {
        $fileContents = file_get_contents('./module/AclUser/test/Mock/image/very_small.png');
        return [
            'path' => [
                'to' => [
                    'layout' => [
                        'images' => [
                            'layoutImageOne.png' => $fileContents,
                            'layoutImageTwo.png' => $fileContents,
                        ],
                    ],
                    'inline' => [
                        'images' => [
                            'inlineImageOne.png' => $fileContents,
                            'inlineImageTwo.png' => $fileContents,
                        ],
                    ],
                    'images' => [
                        'viewImageOne.png' => $fileContents,
                        'viewImageTwo.png' => $fileContents,
                    ],
                    'pdfs' => [
                        'pdfOne.pdf' => '',
                        'pdfTwo.pdf' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get array of layout images
     *
     */
    public function getLayoutImages()
    {
        return [
            'layoutImageOne' =>
            ['type' => 'image/png', 'filepath' => vfsStream::url('root/path/to/layout/images/layoutImageOne.png')],
            'layoutImageTwo' =>
            ['type' => 'image/png', 'filepath' => vfsStream::url('root/path/to/layout/images/layoutImageTwo.png')]
        ];
    }

    /**
     * Get array of layout images
     */
    public function getInlineImages()
    {
        return ['first' =>
            ['type' => 'image/png', 'filepath' => vfsStream::url('root/path/to/images/viewImageOne.png')],
            'second' =>
            ['type' => 'image/png', 'filepath' => vfsStream::url('root/path/to/images/viewImageTwo.png')],
        ];
    }

}
