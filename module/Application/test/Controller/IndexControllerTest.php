<?php

/**
 * Class IndexControllerTest 
 *
 * @package     ApplicationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ControllerMockBuilder;
use Zend\Stdlib\Parameters;

/**
 * Test various aspects of Application\Controller\IndexController
 * 
 * @package     ApplicationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class IndexControllerTest extends AbstractHttpControllerTestCase
{

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
    }

    /**
     * Test that base route / is redirected to home page for user that is not logged in
     */
    public function testBaseRouteActionIsRedirectedToHomeForUnAuthenticateduser()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('application');
        $this->assertControllerName('index'); // as specified in router's controller name alias
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('public', ['controller' => 'index', 'action' => 'index']);
    }

    /**
     * Test that base route / is redirected to home page for user that IS logged in
     */
    public function testBaseRouteActionIsRedirectedToHomeForBasicAuthenticateduser()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('application');
        $this->assertControllerName('index'); // as specified in router's controller name alias
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('public', ['controller' => 'index', 'action' => 'index']);
    }

    /**
     * Test that index page is rendered for basic (by extension all) logged in users
     */
    public function testIndexPageIsrRenderedForLoggedInUser()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/nl_NL/index/index', 'GET');
        $this->assertQuery('.container .row .col-md-12');
        $this->assertModuleName('Application');
        $this->assertControllerName('index');
        $this->assertControllerClass('IndexController');
        $this->assertActionName('index');
    }

    /**
     * Test that index page is rendered for basic (by extension all) logged in users
     */
    public function testIndexPageIsrRenderedForGuestUser()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/index/index', 'GET');
        $this->assertQuery('.container .row .col-md-12');
        $this->assertModuleName('Application');
        $this->assertControllerName('index');
        $this->assertControllerClass('IndexController');
        $this->assertActionName('index');
    }

    /**
     * Ensure that guest user can access about page
     */
    public function testGuestUserCanAccessAboutAction()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/index/about', 'GET');
        $this->assertQuery('.container .row .col-md-12');
        $this->assertModuleName('Application');
        $this->assertControllerName('index');
        $this->assertControllerClass('IndexController');
        $this->assertActionName('about');
    }

    /**
     * Ensure that basic (and by extension all users) user can access about page
     */
    public function testAuthenticatedUserCanAccessAboutAction()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/index/about', 'GET');
        $this->assertQuery('.container .row .col-md-12');
        $this->assertModuleName('Application');
        $this->assertControllerName('index');
        $this->assertControllerClass('IndexController');
        $this->assertActionName('about');
    }

    /**
     * Test that an invalid route does not crash application
     */
    public function testInvalidRouteDoesNotCrash()
    {
        $this->dispatch('/invalid/route', 'GET');
        $this->assertResponseStatusCode(404);
    }

}
