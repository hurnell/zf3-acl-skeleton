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
use TranslateTest\Mock\TranslationServiceMockBuilder;
use org\bovigo\vfs\vfsStream;
use Zend\Mvc\Controller\PluginManager;

/**
 * Test various aspects of Application\Controller\IndexController
 * 
 * @package     ApplicationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 * 
 */
if (!isset($_SESSION)) {
    // $_SESSION = array();
}

class SingleTest extends AbstractHttpControllerTestCase
{

//public static $shared_session = array(  ); 
    protected $helper;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        //$_SESSION = SingleTest::$shared_session;
        $this->setApplicationConfig(TranslationServiceMockBuilder::getConfig(true));
        parent::setUp();
        //$this->builder = new TranslationServiceMockBuilder($this);
    }

    public function testTranslateViewHelperThrowsError()
    {
        $helper = $this->getApplicationServiceLocator()->get('ViewHelperManager')->get("translate");
        var_dump(get_class($helper));
        $this->assertTrue(true);
    }

}
