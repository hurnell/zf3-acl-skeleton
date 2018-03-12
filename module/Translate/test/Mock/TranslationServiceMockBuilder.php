<?php

/**
 * Class TranslationServiceMockBuilder 
 *
 * @package     TranslateTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace TranslateTest\Mock;

use AclUserTest\Mock\MockBuilder;
use Zend\Mvc\MvcEvent;
use Zend\Router\RouteMatch;
use Translate\Service\TranslationManager;
use Translate\Service\LanguageManager;
use Translate\Service\TranslationSaver;
use Translate\View\Helper\FlagNavigation;
use org\bovigo\vfs\vfsStream as FileStream;
use Zend\View\Renderer\PhpRenderer;
use Gettext\Translations;

/**
 * Contains logic for mocking Services
 * 
 * @package     TranslateTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class TranslationServiceMockBuilder extends MockBuilder
{

    /**
     * Real LanguageManager
     * 
     * @var LanguageManager 
     */
    protected $languageManager;
    protected $languages = ['en_EN' => 'Enabled', 'en_GB' => 'English', 'av_AV' => 'available', 'mi_MI' => 'Missing'];

    /**
     * Mock Translate\Service\TranslationManager
     * and inject new Translate\Service\LanguageManager
     * 
     * @return Mock_TranslationManager_***
     */
    public function mockTranslationManagerDependancies()
    {

        $languageManager = new LanguageManager($this->languages);
        $event = new MvcEvent();
        $routeMatch = new RouteMatch([
            'locale' => 'en_EN',
            'controller' => 'user-auth',
            'action' => 'login'
        ]);
        $event->setRouteMatch($routeMatch);
        $languageManager->onDispatch($event);
        $this->languageManager = $languageManager;
        $this->setService('languageManager', $languageManager);
        $translationManager = $this->buildService(TranslationManager::class);
        $this->setService(TranslationManager::class, $translationManager);
        return $translationManager;
    }

    /**
     * Create (real|new) Translate\Service\TranslationSaver
     * 
     * @param boolean $withGlobal whether to include global in mocked file_patters
     * @return TranslationSaver new
     */
    public function mockTranslationSaverService($withGlobal = true)
    {
        $translationManager = $this->mockTranslationManagerDependancies($withGlobal);
        $translationManager->setDirectories($this->getDirectories());
        $this->setService(TranslationManager::class, $translationManager);
        $filePatters = $this->getFilePatterns($withGlobal);

        $translationSaver = new TranslationSaver($filePatters);
        $translator = ['locale' => 'en_EN',
            'translation_file_patterns' => $filePatters];
        $config = $this->getService('config');
        $config ['translator'] = $translator;
        $this->setService('config', $config);
        $translationSaver->setTranslationManager($translationManager);
        $translatorFactory = $this->buildService(\Translate\Mvc\I18n\TranslatorFactory::class);
        $this->setService('MvcTranslator', $translatorFactory);
        return $translationSaver;
    }

    public function mockMoFiles()
    {
        $directories = $this->getDirectories();
        $locales = array_keys($this->languages);
        foreach ($directories as $directory) {
            foreach ($locales as $locale) {
                if (is_file($directory . '/' . $locale . '.po')) {
                    $poFileLocation = $directory . '/' . $locale . '.po';
                    $moFileLocation = $directory . '/' . $locale . '.mo';
                    $translator = Translations::fromPoFile($poFileLocation);
                    $translator->toMoFile($moFileLocation);
                }
            }
        }
    }

    /**
     * Mock Zend\View\Renderer\PhpRenderer
     * 
     * @return Mock_Phprenderer_***
     */
    public function mockPhpRenderer()
    {
        $renderer = $this->test->getMockBuilder(PhpRenderer::class)->disableOriginalConstructor()->getMock();
        $renderer->expects($this->test->any())->method('__call')->willReturn('the-url');
        return $renderer;
    }

    /**
     * Build Translate\Service\LanguageManager with empty configuration array
     * 
     * @return Translate\Service\LanguageManager
     */
    public function getBadLanguageManager()
    {
        $this->setService('config', []);
        return $this->buildService('languageManager');
    }

    /**
     * Get new Translate\View\Helper\FlagNavigation
     * 
     * @return FlagNavigation
     */
    public function getFlagNavigationViewHelper()
    {
        $this->mockTranslationManagerDependancies();
        return new FlagNavigation($this->languageManager);
    }

    /**
     * Create fake directory structure for vfsStream
     * 
     * @param string $translated translation for "All rights reserved"
     * @return array
     */
    public function getFakeDirectoryStructure($translated = 'Alle rechten vbehouden')
    {
        return [
            'module' => [
                'Translate' => [
                    'language' => [
                        'en_EN.po' => '
msgid ""
msgstr ""  

msgid "Welcome"
msgstr "Welcome en_EN Translate"

msgid "You have been successfully logged in."
msgstr "You have been successfully logged in."
                        ',
                        'av_AV.po' => '
msgid ""
msgstr ""  

msgid "Welcome"
msgstr "Welcome av_AV Translate"

msgid "All rights reserved"
msgstr "' . $translated . '"
                        ',
                    ]
                ],
                'Application' => [
                    'language' => [
                        'en_EN.po' => '
msgid ""
msgstr ""  

msgid "Soumi"
msgstr "Finnish"

msgid "Welcome"
msgstr "Welcome en_EN Application"

msgid "Enabled Languages"
msgstr "Enabled Languages"
                        ',
                        'en_GB.po' => '
msgid ""
msgstr ""  

msgid "Soumi"
msgstr "Finnish"


msgid "Welcome"
msgstr "Welcome en_GB Application"

msgid "Enabled Languages"
msgstr "Enabled Languages"                    
                        ',
                        'av_AV.po' => '
msgid ""
msgstr ""  

msgid "en_GB"
msgstr "Engels"

msgid "Welcome"
msgstr "Welcome av_AV Application"

msgid "Soumi"
msgstr "Fins"
                        ',
                        'mi_MI.po' => '',
                    ]
                ],
                'Social' => [
                    'language' => [
                    ],
                ],
                'AclUser' => [
                    'language' => [
                        'en_EN.po' => '
msgid ""
msgstr ""  

msgid "Soumi"
msgstr "Finnish"

msgid "Welcome"
msgstr "Welcome en_EN AclUser"

msgid "Enabled Languages"
msgstr "Enabled Languages"
                        ',
                    ],
                ],
            ],
        ];
    }

    /**
     * Get array of vfsStream directories
     * 
     * @return array
     */
    public function getDirectories()
    {
        return [
            'global' => FileStream::url('root/module/Application/language'),
            'translate' => FileStream::url('root/module/Translate/language'),
            'default' => FileStream::url('root/module/Social/language'),
        ];
    }

    /**
     * Get message translation from mocked (vfsStream) translation file(s)
     * 
     * @param vfsStream $vfs
     * @param string $path
     * @param string $saught the string to be translated
     * @return boolean
     */
    public function getMsgStrByMsgId($vfs, $path, $saught)
    {
        $contents = $vfs->getChild($path)->getContent();
        $lines = explode("\n", $contents);
        $index = false;
        foreach ($lines as $idx => $line) {
            $simple = trim(str_replace(['msgid', '"'], '', $line));
            if (trim($saught) === $simple) {
                $index = $idx + 1;
            }
        }
        if (false !== $index) {
            return trim(str_replace(['msgstr', '"'], '', $lines [$index]));
        }
        return false;
    }

    /**
     * Get array of file patterns
     * 
     * @param boolean $withGlobal
     * @return array of file patters
     */
    public function getFilePatterns($withGlobal = true)
    {
        $out = [];
        $former = [
            'type' => 'gettext',
            'base_dir' => '',
            'pattern' => '%s.mo',
            'controllers' => []
        ];
        $types = [
            'social' => FileStream::url('root/module/Social/language'),
            'index' => FileStream::url('root/module/Application/language'),
            'default' => FileStream::url('root/module/Application/language'),
            'translate' => FileStream::url('root/module/Translate/language'),
        ];
        if ($withGlobal) {
            $types['global'] = FileStream::url('root/module/Application/language');
        }
        foreach ($types as $k => $v) {
            $former['base_dir'] = $v;
            $former['controllers'] = [$k];
            $former['text_domain'] = $k;
            $out[] = $former;
        }
        return $out;
    }

}
