<?php

/**
 * Class TranslationControllerTest 
 *
 * @package     TranslationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace TranslateTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ControllerMockBuilder;

/**
 * Test various aspects of AclUser\Controller\UserController
 *
 * @package     TranslationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class TranslationControllerTest extends AbstractHttpControllerTestCase
{

    /**
     * Instance of class that is used to mock AclUser\Service\UserManager,
     * Zend\Authentication\AuthenticationService and replace AccessControlList 
     * 
     * @var ControllerMockBuilder 
     */
    protected $builder;

    /**
     * FlashMessengerTestCaseHelper is created in MockBuilder
     * 
     * @see \AclUserTest\Mock\MockBuilder::createFlashMessengerTestCaseHelper
     * @var \AclUserTest\Mock\FlashMessengerTestCaseHelper 
     */
    protected $fmtc;

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

    /**
     * Test that uber-translator can access /en_GB/translate/index
     */
    public function testAuthorisedUserCanAccessIndexAction()
    {
        $this->builder->specificAuthorised('uber-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/index', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('index');
    }

    /**
     * Test that basic user cannot access /en_GB/translate/index
     */
    public function testUnAuthorisedUserCannotAccessIndexAction()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/translate/index', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('index');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You do not have permission to visit the requested page.');
    }

    /**
     * Test that site-language-admin user can access /en_GB/translate/manage-system-languages
     */
    public function testAuthorisedUserCanAccessManageSystemLanguagesAction()
    {
        $this->builder->specificAuthorised('site-language-admin')->finaliseAcl();

        $this->dispatch('/en_GB/translate/manage-system-languages', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('manage-system-languages');
    }

    /**
     * Test that uber-translator user cannot access /en_GB/translate/manage-system-languages
     */
    public function testUnAuthorisedUserCannotAccessManageSystemLanguagesAction()
    {
        $this->builder->specificAuthorised('uber-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/manage-system-languages', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('manage-system-languages');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You do not have permission to visit the requested page.');
    }

    /**
     * Test that uber-translator user can access /en_GB/translate/edit/en_GB/all
     */
    public function testAuthorisedUserCanAccessEditLanguagesAction()
    {
        $this->builder->specificAuthorised('uber-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/edit/en_GB/all', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('edit');
    }

    /**
     * Test that dutch-translator user can access /en_GB/translate/edit/en_GB/all
     */
    public function testSemiAuthorisedUserCannotAccessSpecificLanguagesAction()
    {
        $this->builder->specificAuthorised('dutch-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/edit/en_GB/all', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('edit');
        $this->assertRedirectRegex('/\/translate\/index/');
    }

    /**
     * Test that dutch-translator user can access /en_GB/translate/edit/nl_NL/all
     */
    public function testDutchAuthorisedUserCanAccessEditDutchLanguagesEntry()
    {
        $this->builder->specificAuthorised('dutch-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/edit-translation/nl_NL/all/0/0', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('edit-translation');
    }

    /**
     * Test that dutch-translator user cannot access /en_GB/translate/edit/en_GB/all
     */
    public function testDutchAuthorisedUserCannotAccessEditEnglishLanguagesEntry()
    {
        $this->builder->specificAuthorised('dutch-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/edit-translation/en_GB/all/0/0', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('edit-translation');
        $this->assertRedirectRegex('/\/translate\/index/');
    }

    /**
     * Test that dutch-translator user can post to /en_GB/translate/edit/nl_NL/all
     */
    public function testDutchAuthorisedUserCanPostToEditDutchLanguagesEntry()
    {
        $this->builder->specificAuthorised('dutch-translator')->finaliseAcl();

        $this->dispatch('/en_GB/translate/edit-translation/nl_NL/all/0/0', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('edit-translation');
    }

    /**
     * Test that dutch-translator user can post to /en_GB/translate/edit/nl_NL/all
     */
    public function testDutchAuthorisedUserCanPostAndValidateToEditDutchLanguagesEntry()
    {
        $this->builder->specificAuthorised('dutch-translator')->setupTranslationFormMock()->finaliseAcl();

        $form = new \Translate\Form\TranslationForm();
        $form->prepare();
        $params = [
            'csrf' => $form->get('csrf')->getValue(),
            'msgid' => 'value1',
            'index' => '0',
            'idx' => '0',
            'locale' => 'nl_NL',
            'type' => 'all',
            'filepath' => 'hhhhh'
        ];
        $this->dispatch('/en_GB/translate/edit-translation/nl_NL/all/0/0', 'POST', $params);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('edit-translation');
    }

    /**
     * Test that site-language-admin user can update available languages
     */
    public function testAuthorisedUserCannotUpdateAvailableLanguagesWithGetRequest()
    {
        $this->builder->specificAuthorised('site-language-admin')->finaliseAcl();
        $this->dispatch('/en_GB/translate/ajax-update-available-languages', 'GET', ['change' => 'disable', 'locale' => 'el_GR']);
        $this->assertResponseStatusCode(500);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('ajax-update-available-languages');
    }

    public function testApplicationThrowsErrorForAuthorisedUserWhenPostingINcorrectChangeParam()
    {
        $this->builder->specificAuthorised('site-language-admin')->finaliseAcl();
        $this->dispatch('/en_GB/translate/ajax-update-available-languages', 'POST', ['change' => 'unknown_change', 'locale' => 'el_GR']);
        $this->assertResponseStatusCode(500);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('ajax-update-available-languages');
    }

    public function testAuthorisedUserCannotDisableLanguagesThatDoesNotExist()
    {
        $this->builder->specificAuthorised('site-language-admin')->finaliseAcl();

        $this->dispatch('/en_GB/translate/ajax-update-available-languages', 'POST', ['change' => 'disable', 'locale' => 'non_EXISTENT']);
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('ajax-update-available-languages');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('result', $json, 'json response should contain key result');
        $this->assertNotTrue($json['result'], 'json response should return false for result key');
    }

    public function testAuthorisedUserCannotEnableLanguagesThatDoesNotExist()
    {
        $this->builder->specificAuthorised('site-language-admin')->finaliseAcl();

        $this->dispatch('/en_GB/translate/ajax-update-available-languages', 'POST', ['change' => 'enable', 'locale' => 'non_EXISTENT']);
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Translate');
        $this->assertControllerName('translate');
        $this->assertControllerClass('TranslationController');
        $this->assertActionName('ajax-update-available-languages');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('result', $json, 'json response should contain key result');
        $this->assertNotTrue($json['result'], 'json response should return false for result key');
    }

}
