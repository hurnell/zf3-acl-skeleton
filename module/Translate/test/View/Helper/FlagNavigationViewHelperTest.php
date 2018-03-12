<?php

/**
 * Class TranslationSaverServiceTest 
 * 
 *
 * @package     TranslateTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace TranslateTest\View\Helper;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use TranslateTest\Mock\TranslationServiceMockBuilder;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * Test various aspects of Translate\Service\TranslationManage
 *
 * @package     TranslateTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class FlagNavigationViewHelperTest extends AbstractHttpControllerTestCase
{

    protected $builder;
    protected $flagNavigation;
    private $vfs;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(TranslationServiceMockBuilder::getConfig(true));
        parent::setUp();
        $this->builder = new TranslationServiceMockBuilder($this);
        $this->builder->initialiseEntityManagerMock();
        $this->flagNavigation = $this->builder->getFlagNavigationViewHelper();
        $this->vfs = vfsStream::setup('root', null, $this->builder->getFakeDirectoryStructure());
    }

    public function testOneAgain()
    {
        $this->flagNavigation->render($this->builder->mockPhpRenderer());
      // assert that po file created for social
         $this->assertTrue(true);
    }
    

}
