<?php

/**
 * Class AuthAdapterServiceTest 
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

/**
 * Test various aspects of Translate\Service\TranslationManage
 *
 * @package     TranslateTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class TranslationManagerServiceTest extends AbstractHttpControllerTestCase
{

    protected $builder;
    protected $translationManager;
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
        $this->translationManager = $this->builder->mockTranslationManagerDependancies();
        $this->vfs = vfsStream::setup('root', null, $this->builder->getFakeDirectoryStructure());
        $this->translationManager->setDirectories($this->builder->getDirectories());
    }

    public function testApplicationCanGetAllLocales()
    {
        $locales = $this->translationManager->getAllLocales();
        $this->assertTrue(is_array($locales), 'getAllLocales should return an array');
        $this->assertContains('en_EN', $locales, 'getAllLocales should have one value that is en_EN');
        $this->assertContains('av_AV', $locales, 'getAllLocales should have one value that is av_AV');
        $this->assertContains('mi_MI', $locales, 'getAllLocales should have one value that is mi_MI');
    }

    public function testApplicationCanGetPoFiles()
    {
        $poFiles = $this->translationManager->getPoFiles();
        $this->assertTrue(is_array($poFiles), 'getPoFiles should return an array');
        $this->assertContains(['av_AV' => 'vfs://root/module/Application/language/av_AV.po'], $poFiles);
        $this->assertContains(['en_EN' => 'vfs://root/module/Application/language/en_EN.po'], $poFiles);
        $this->assertContains(['av_AV' => 'vfs://root/module/Translate/language/av_AV.po'], $poFiles);
        $this->assertContains(['en_EN' => 'vfs://root/module/Translate/language/en_EN.po'], $poFiles);
    }

    public function testApplicationCanGetLocalesWithTranslations()
    {
        $localesWithTranslations = $this->translationManager->getLocalesWithTranslations();
        $this->assertTrue(is_array($localesWithTranslations), 'getLocalesWithTranslations should return an array');
        $this->assertContains('en_EN', $localesWithTranslations, 'getAllLocales should have one value that is en_EN');
        $this->assertContains('av_AV', $localesWithTranslations, 'getAllLocales should have one value that is av_AV');
    }

    public function testApplicationCanCheckLocaleIsEnabled()
    {
        $enEnIsEnabled = $this->translationManager->checkLocaleIsEnabled('en_EN');
        $this->assertTrue($enEnIsEnabled, 'application should have en_EN enabled');
        $avAvIsEnabled = $this->translationManager->checkLocaleIsEnabled('av_AV');
        $this->assertFalse($avAvIsEnabled, 'application should not have av_AV enabled');
        $miMIIsEnabled = $this->translationManager->checkLocaleIsEnabled('mi_MI');
        $this->assertFalse($miMIIsEnabled, 'application should not have mi_MI enabled');
    }

    public function testApplicationCanGetLocale()
    {
        $locale = $this->translationManager->getLocale();
        $this->assertEquals('en_EN', $locale, 'application should return en_EN as locale');
    }

    public function testApplicationCanGetUrl()
    {
        $url = $this->translationManager->getUrl('av_AV');
        $this->assertEquals('/av_AV/user-auth/login', $url, 'application should return "/av_AV/user-auth/login" for getUrl("av_AV")');
    }

    public function testApplicationCanGetTextDomain()
    {
        $textDomain = $this->translationManager->getTextDomain();
        $this->assertEquals('user-auth', $textDomain, 'application should return "user-auth" as textDomain');
    }

    public function testApplicationCanGetAllTranslations()
    {
        $all = $this->translationManager->getAllTranslations('all', 'en_EN');
        $this->assertTrue(is_array($all), 'getAllTranslations should return an array');
        $this->assertArrayHasKey('msgid', $all[0], 'each element returned by getAllTranslations should have msgid as one key');
        $this->assertArrayHasKey('msgstr', $all[0], 'each element returned by getAllTranslations should have msgstr as one key');
        $this->assertArrayHasKey('filepath', $all[0], 'each element returned by getAllTranslations should have filepath as one key');
        $this->assertArrayHasKey('idx', $all[0], 'each element returned by getAllTranslations should have idx as one key');
        $this->assertArrayHasKey('index', $all[0], 'each element returned by getAllTranslations should have index as one key');
    }

    public function testApplicationCanGetSpecificTranslationArray()
    {
        $translation = $this->translationManager->getTranslationArray('all', 'av_AV', 1, 1);
        $this->assertTrue(is_array($translation), 'getTranslationArray should return an array');
        $this->assertArrayHasKey('msgid', $translation, 'each element returned by getTranslationArray should have msgid as one key');
        $this->assertArrayHasKey('msgstr', $translation, 'each element returned by getTranslationArray should have msgstr as one key');
        $this->assertArrayHasKey('filepath', $translation, 'each element returned by getTranslationArray should have filepath as one key');
        $this->assertArrayHasKey('idx', $translation, 'each element returned by getTranslationArray should have idx as one key');
        $this->assertArrayHasKey('index', $translation, 'each element returned by getTranslationArray should have index as one key');
        $this->assertContains('All rights reserved', $translation, 'getTranslationArray with given args should return All rights reserved as one element of array');
        $this->assertContains('Alle rechten vbehouden', $translation, 'getTranslationArray with given args should return All rights reserved as one element of array');
    }

    public function testApplicationCanUpdateTranslation()
    {
        $data = [
            'locale' => 'av_AV',
            'idx' => '1',
            'msgstr' => 'Alle rechten voorbehouden',
            'filepath' => 'vfs://root/module/Translate/language/av_AV.po',
            'msgid' => 'All rights reserved'
        ];
        $found = $this->builder->getMsgStrByMsgId($this->vfs, 'module/Translate/language/av_AV.po', $data['msgid']);
        $this->assertNotEquals($data ['msgstr'], $found);
        $this->translationManager->updateTranslation($data);
        $this->assertFileExists('vfs://root/module/Translate/language/av_AV.mo');
        $foundtwo = $this->builder->getMsgStrByMsgId($this->vfs, 'module/Translate/language/av_AV.po', $data['msgid']);
        $this->assertEquals($data ['msgstr'], $foundtwo);
    }

}
