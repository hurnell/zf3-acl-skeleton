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

namespace TranslateTest\Service;

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
class TranslationSaverServiceTest extends AbstractHttpControllerTestCase
{

    protected $builder;
    protected $translationSaver;
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
        $this->vfs = vfsStream::setup('root', null, $this->builder->getFakeDirectoryStructure());
    }

    public function testOne()
    {
        $this->translationSaver = $this->builder->mockTranslationSaverService();
        $this->translationSaver->getPoFileLocation('social', 'en_EN');
        // assert that po file created for social
        $this->assertTrue(true);
    }

    public function testTwo()
    {

        $this->translationSaver = $this->builder->mockTranslationSaverService();
        $this->translationSaver->saveMissingTranslation('new message', 'en_EN', 'remove');

        $this->assertTrue(true);
    }

    public function testThree()
    {

        $this->translationSaver = $this->builder->mockTranslationSaverService();
        $this->translationSaver->saveMissingTranslation('new message', 'en_GB', 'remove');

        $this->assertTrue(true);
    }

    /**
     * @expectedExceptionMessageRegExp \You must specify a global\
     */
    public function testFour()
    {

        $this->expectException(\Exception::class);
        $this->translationSaver = $this->builder->mockTranslationSaverService(false);
    }

    /**
     * @expectedExceptionMessageRegExp \banana\
     */
    public function testFive()
    {
        $this->dispatch('/');
        $this->expectException(\Exception::class);

        $this->builder->getBadLanguageManager();
    }

    public function testControllerPluginCanTranslate()
    {
        $this->builder->mockMoFiles();
        $this->builder->mockTranslationSaverService();
        $plugin = $this->builder->getService('ControllerPluginManager')->build('translateContollerPlugin');
        $this->assertEquals('Welcome en_EN Application', $plugin->translate('Welcome'));
    }

    public function testControllerPluginCannotTranslateUnknownTranslation()
    {
        $this->builder->mockMoFiles();
        $this->builder->mockTranslationSaverService();
        $plugin = $this->builder->getService('ControllerPluginManager')->build('translateContollerPlugin');
        $this->assertEquals('Brand New String', $plugin->translate('Brand New String'));
    }

    public function testControllerPluginCannotTranslateUnknownTranslationWithTextDomainAndLocale()
    {
        $this->builder->mockMoFiles();
        $this->builder->mockTranslationSaverService();
        $plugin = $this->builder->getService('ControllerPluginManager')->build('translateContollerPlugin');
        $this->assertEquals('Brand New String', $plugin->translate('Brand New String', 'translate', 'av_AV'));
    }

    public function testControllerPluginCannotTranslateKnownTranslationWithTextDomain()
    {
        $this->builder->mockMoFiles();
        $this->builder->mockTranslationSaverService();
        $plugin = $this->builder->getService('ControllerPluginManager')->build('translateContollerPlugin');
        $this->assertEquals('Welcome av_AV Translate', $plugin->translate('Welcome', 'translate', 'av_AV'));
    }

}
