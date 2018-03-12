<?php

/**
 * Class TranslationSaver
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Service;

use Gettext\Translations;
use Translate\Service\TranslationManager;

/**
 * Class that is responsible for saving translated messages.
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationSaver implements TranslationSaverInterface
{

    /**
     * Array of PO/MO file directories
     * 
     * @var array 
     */
    protected $directories = [];

    /**
     * Present text domain key = controller alias
     * 
     * @var string
     */
    protected $textDomainKey;

    /**
     * The Translation Manager
     * 
     * @var TranslationManager 
     */
    protected $translationManager;

    /**
     * 
     * 
     * @var array of Gettext\Translations
     */
    protected $translators = [];

    /**
     * Flag to show whether translation file exists
     * 
     * @var boolean
     */
    protected $translationFileExists = false;

    /**
     * Present PO location file
     * 
     * @var string the location of (present) PO file
     */
    protected $poFile;

    /**
     * 
     * 
     * @var string the location of (present) MO file
     */
    protected $moFile;

    /**
     * Instantiate TranslationSaver object injecting file patters  
     * 
     * @param array $filePatterns
     */
    public function __construct($filePatterns)
    {
        foreach ($filePatterns as $pattern) {
            if (array_key_exists('base_dir', $pattern) && array_key_exists('controllers', $pattern)) {
                $this->rationaliseFilePattern($pattern);
            }
        }
    }

    /**
     * Transform controller patterns into location array
     * Controller alias as key and directory as value
     * 
     * @param array $pattern
     */
    private function rationaliseFilePattern($pattern)
    {

        foreach ($pattern['controllers'] as $controller) {
            if (!array_key_exists($controller, $this->directories)) {
                $this->directories[$controller] = $pattern['base_dir'];
            }
        }
    }

    /**
     * Set the translation manager 
     * 
     * @param TranslationManager $translationManager
     * @throws \Exception if global 
     */
    public function setTranslationManager(TranslationManager $translationManager)
    {
        if (!array_key_exists('global', $this->directories) || false == realpath($this->directories['global'])) {
            throw new \Exception('You must specify a global (controllers array) translation save path  (usually in global.php');
        }
        $this->translationManager = $translationManager;
        $this->translationManager->setDirectories($this->directories);
    }

    /**
     * Get file location of PO file for specific text domain (controller alias) and locale
     * 
     * @param string $textDomainKey
     * @param string $locale
     * @return string
     */
    public function getPoFileLocation($textDomainKey, $locale)
    {
        $folder = $this->directories['global'];
        $this->poFile = realpath($folder) . '/' . $locale . '.po';

        if (array_key_exists($textDomainKey, $this->directories) && file_exists($this->directories[$textDomainKey])) {
            $folder = $this->directories[$textDomainKey];
            $this->poFile = realpath($folder) . '/' . $locale . '.po';
        }
        if (realpath($folder) && !file_exists($this->poFile)) {
            $this->createNewPoFile($this->poFile, $locale);
        }
        $this->moFile = realpath($folder) . '/' . $locale . '.mo';
        $this->translationFileExists = file_exists($this->poFile);

        return $this->poFile;
    }

    /**
     * Create PO file when missing
     * 
     * @param string $location
     * @param string $locale
     */
    public function createNewPoFile($location, $locale)
    {
        $allLocales = $this->translationManager->getAllLocales();
        if (in_array($locale, $allLocales) && $this->translationManager->checkLocaleIsEnabled($locale)) {
            $cleanLocation = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $location);
            $date = new \DateTime();
            $locale = $this->translationManager->getLocale();
            $text = 'msgid ""' . "\n" . 'msgstr ""' . "\n";
            $text .= '"Project-Id-Version: Hurnell User Demo"' . "\n";
            $text .= '"Report-Msgid-Bugs-To: "' . "\n";
            $text .= '"POT-Creation-Date: "' . $date->getTimestamp() . '"' . "\n";
            $text .= '"Last-Translator: "' . "\n";
            $text .= '"Language: ' . $locale . "\n";
            $text .= '"MIME-Version: 1.0"' . "\n";
            $text .= '"Content-Type: text/plain; charset=UTF-8"' . "\n";
            $text .= '"Content-Transfer-Encoding: 8bit"' . "\n";
            $text .= '"X-Poedit-KeywordsList: translate"' . "\n";
            $text .= '"X-Poedit-Basepath: ."' . "\n";
            $text .= '"X-Poedit-SearchPath-0: .."' . "\n";
            file_put_contents($cleanLocation, $text);
        }
    }

    /**
     * Get the Gettext\Translations object
     * 
     * @param string $textDomainKey
     * @param string $locale
     * @return Gettext\Translations|false
     */
    public function getTranslator($textDomainKey, $locale)
    {
        $poFileLocation = $this->getPoFileLocation($textDomainKey, $locale);
        if ($textDomainKey != $this->textDomainKey && $this->translationFileExists) {
            if (!array_key_exists($textDomainKey, $this->translators)) {
                $this->translators[$textDomainKey] = Translations::fromPoFile($poFileLocation);
            }
            $this->textDomainKey = $textDomainKey;
        }
        $result = false;
        if (array_key_exists($this->textDomainKey, $this->translators)) {
            $result = $this->translators[$this->textDomainKey];
        }
        return $result;
    }

    /**
     * Save translation for particular message if missing
     * @param string $message
     * @param string $locale
     * @param string $translation
     */
    public function saveMissingTranslation($message, $locale, $translation)
    {
        $textDomainKey = $this->translationManager->getTextDomain();
        $translator = $this->getTranslator($textDomainKey, $locale);
        if ($this->translationFileExists && $translator && !$this->messageAlreadyExists($message, $translator)) {
            $translation = ' ';
            if ('en_GB' == $locale) {
                $translation = $message;
            }
            $insertedTranslation = $translator->insert('', $message);
            $insertedTranslation->addReference('URI: ' . $this->translationManager->getUrl());
            $insertedTranslation->setTranslation($translation);
            $this->updateMoFile($translator);
        }
    }

    /**
     * Update MO file based on corresponding PO file
     * 
     * @param Translations $translator
     */
    protected function updateMoFile($translator)
    {
        $translator->toPoFile($this->poFile);
        $translator->toMoFile($this->moFile);
    }

    /**
     * Check whether message already exists in translator
     * 
     * @param string $message
     * @param Translations $translator
     * @return boolean whether message already exists in the translator
     */
    public function messageAlreadyExists($message, $translator)
    {
        $exists = $translation = $translator->find(null, $message) !== false;
        return $exists;
    }

}
