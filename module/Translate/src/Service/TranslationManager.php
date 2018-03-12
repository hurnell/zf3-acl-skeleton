<?php

/**
 * Class TranslationManager
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Service;

use Gettext\Translations;
use Zend\Mvc\MvcEvent;

/**
 * This is the factory class for TranslationManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationManager
{

    /**
     * Array of directories where PO files are stored
     * 
     * @var array
     */
    protected $directories = [];

    /**
     * Array of languages that are used in application 
     * 
     * @var array
     */
    protected $languages;

    /**
     * Language Manager that handles logic surrounding the site languages
     * 
     * @var Translate\Service\LanguageManager 
     */
    protected $languageManager;

    /**
     * instantiate class and inject array of languages and array of directories that contain the language flag images
     * 
     * @param Translate\Service\LanguageManager $languageManager
     */
    public function __construct($languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * Set up the directories array where the PO files are saved
     * 
     * @param array $directories
     */
    public function setDirectories($directories)
    {
        $this->directories = [];
        foreach ($directories as $directory) {
            $realDirectory = realpath($directory);
            if ($realDirectory && !in_array($realDirectory, $this->directories)) {
                $this->directories[] = $realDirectory;
            }
        }
    }

    /**
     * Get locales that have corresponding PO files
     * 
     * @return array
     */
    public function getLocalesWithTranslations()
    {
        $locales = [];
        $poFiles = $this->getPoFiles();
        foreach ($poFiles as $file) {
            $keys = array_keys($file);
            foreach ($keys as $key) {
                if (!in_array($key, $locales)) {
                    $locales[] = $key;
                }
            }
        }
        return $locales;
    }

    /**
     * Get array containing the (absolute) file paths of all PO files
     * 
     * @param string|boolean $locale
     * @return array
     */
    public function getPoFiles($locale = false)
    {
        $poFiles = [];
        foreach ($this->directories as $directory) {
            $files = scandir($directory);
            foreach ($files as $file) {
                $filepath = $directory . DIRECTORY_SEPARATOR . $file;
                if (false !== $poFile = $this->checkForPoFiles($file, $filepath, $locale)) {
                    $poFiles[] = $poFile;
                }
            }
        }
        return $poFiles;
    }

    /**
     * Get array of all translations for a particular locale
     * 
     * @param string $type untranslated or all
     * @param string $locale the locale for which translations are requested
     * @return array array containing the translations
     */
    public function getAllTranslations($type, $locale)
    {
        $files = $this->getPoFiles($locale);
        $translations = [];
        foreach ($files as $idx => $filearray) {
            $translations = $this->readTranslation($filearray[$locale], $translations, $type, $idx);
        }
        return $translations;
    }

    /**
     * Check whether a po file exists for a particular locale
     * 
     * @param string $file
     * @param string $filepath
     * @param string $locale
     * @return array
     */
    protected function checkForPoFiles($file, $filepath, $locale = false)
    {
        $fileParts = explode('.', $file);
        $result = false;
        if (2 == count($fileParts) && 'po' == $fileParts[1] && (false == $locale || $fileParts[0] == $locale)) {
            $parts = explode('_', $fileParts[0]);
            if (2 == count($parts)) {
                $result = [$fileParts [0] => $filepath];
            }
        }
        return $result;
    }

    /**
     * Read the translation for particular language locale and add the $traanslations array
     * 
     * @param string $filepath
     * @param array $translations
     * @param string $type
     * @param int $idx
     * @return array
     */
    protected function readTranslation($filepath, $translations, $type, $idx)
    {
        $translator = Translations::fromPoFile($filepath);
        $index = 0;
        foreach ($translator as $translation) {
            $translated = trim($translation->getTranslation());
            if (($type == 'untranslated' && $translated == '') || $type == 'all') {
                $translations[] = [
                    'msgid' => $translation->getOriginal(),
                    'msgstr' => $translated,
                    'filepath' => $filepath,
                    'idx' => $idx,
                    'index' => $index
                ];
            }
            $index ++;
        }
        return $translations;
    }

    /**
     * Get array of details for message that is currently being translated
     * 
     * @param string $key
     * @param int $line
     * @param string $match
     * @param string $search
     * @param int $idx
     * @param array $lines
     * @param string $filepath
     * @return array
     *//*
      protected function setTranslation($key, $line, $match, $search, $idx, $lines, $filepath)
      {
      $result = false;
      if (strpos($line, $search) === 0) {
      $pos = strpos($line, '"', 7);
      $msgid = substr($line, 7, $pos - 7);
      $nextLine = $lines[$key + 1];
      if (strpos($nextLine, $match) === 0 && $msgid != '') {
      $npos = strpos($nextLine, '"', 8);
      $msgstr = substr($nextLine, 8, $npos - 8);
      $result = [
      'index' => $key,
      'msgid' => $msgid,
      'msgstr' => $msgstr,
      'idx' => $idx,
      'filepath' => $filepath
      ];
      }
      }
      return $result;
      } */

    /**
     * Get an array of parameters showing which PO file contains the translated
     * message
     * 
     * @param string $type all or untranslated
     * @param string $locale the language locale
     * @param int $idx
     * @param int $index
     * @return array 
     */
    public function getTranslationArray($type, $locale, $idx, $index)
    {
        $result = false;
        $translations = $this->getAllTranslations($type, $locale);
        foreach ($translations as $translation) {
            if ($translation['idx'] == $idx && $translation['index'] == $index) {
                $result = $translation;
                break;
            }
        }
        return $result;
    }

    /**
     * Update PO and sync MO file that contains present translation
     * 
     * @param arrray $data
     */
    public function updateTranslation($data)
    {
        $locale = $data['locale'];
        $idx = (int) $data['idx'];
        $msgstr = $data['msgstr'];
        $filepath = $data['filepath'];
        $original = $data['msgid'];
        $poFiles = $this->getPoFiles($locale);

        $filepaths = $poFiles[$idx];
        if ($filepaths[$locale] == $filepath) {
            $translator = Translations::fromPoFile($filepath);
            $translation = $translator->find(null, $original);
            $translation->setTranslation($msgstr);
            $translator->toPoFile($filepath);
            $translator->toMoFile($this->getMoFile($filepath));
        }
    }

    /**
     * Get file path to mo file
     * 
     * @param string $poFile
     * @return string
     */
    protected function getMoFile($poFile)
    {
        return str_replace('.po', '.mo', $poFile);
    }

    /**
     * Get array of available languages
     * 
     * @return array of available languages
     */
    public function getAllLocales()
    {
        return $this->languageManager->getAllLocales();
    }

    /**
     * Check whether the passed locale is enabled within the system
     * 
     * @param string $locale
     * @return boolean
     */
    public function checkLocaleIsEnabled($locale)
    {
        $enabled = $this->languageManager->getEnabledLocales();
        return in_array($locale, $enabled);
    }

    /**
     * Get the text domain = alias of the present controller
     * 
     * @return string alias of given controller
     */
    public function getTextDomain()
    {
        return $this->languageManager->getTextDomain();
    }

    /**
     * Get the URL that is given by the url params
     * 
     * @param string $locale
     * @return string
     */
    public function getUrl($locale = null)
    {
        $params = $this->languageManager->getParams(); //$this->params;
        if (null !== $locale && array_key_exists('locale', $params)) {
            $params['locale'] = $locale;
        }
        return '/' . implode('/', $params);
    }

    /**
     * Get the present locale as indicated by the URL params
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->languageManager->getLocale();
    }

}
