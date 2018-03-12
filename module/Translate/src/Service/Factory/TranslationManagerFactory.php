<?php

/**
 * Class TranslationManagerFactory
 *
 * @package     Translate\Service\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Service\Factory;

use Interop\Container\ContainerInterface;
use Translate\Service\TranslationManager;
use Translate\Service\TranslationSaver;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 *
 * @package     Translate\Service\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationManagerFactory
{

    /**
     * This method creates the TranslationManager service and returns its instance
     * after getting TranslationSaver and setting TranslationManager on same
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return TranslationManager
     * @throws \Exception
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $languageManager = $container->get('languageManager');
        $translationManager = new TranslationManager($languageManager);
        $translationSaver = $container->get(TranslationSaver::class);
        $translationSaver->setTranslationManager($translationManager);
        return $translationManager;
    }

}
