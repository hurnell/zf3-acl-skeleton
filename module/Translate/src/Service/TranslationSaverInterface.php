<?php

/**
 * Class TranslationSaverInterface
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Service;

/**
 * Interface for any translation saver
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
interface TranslationSaverInterface {

    /**
     * Save translation if missing
     * F
     * @param string $message
     * @param string $locale
     * @param string $translation
     */
    public function saveMissingTranslation($message, $locale, $translation);
}
