<?php

/**
 * Class CookieManagerServiceTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;
use Application\Module;

/**
 * Test various aspects of AclUser\Service\AuthAdapter
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class CookieManagerServiceTest extends AbstractHttpControllerTestCase
{

    protected $builder;
    protected $cookieValue;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ServiceMockBuilder::getConfig(true));
        parent::setUp();
        $_COOKIE = [Module::SESSION_NAME => $this->cookieValue];
        define('APPLICATION_ENV', 'production');

        $this->builder = new ServiceMockBuilder($this);
    }

    public function testAuthAdapterServiceCanFailAuthenticateWithoutUser()
    {
        $this->dispatch('/en_GB/user-auth/login', 'GET');
        $cookie = $this->getRequest()->getCookie();
        $this->assertNotEquals(false, $cookie);
        $this->assertTrue($cookie->offsetExists(Module::SESSION_NAME));
        $this->assertEquals($this->cookieValue, $cookie->offsetGet(Module::SESSION_NAME));
    }

}
