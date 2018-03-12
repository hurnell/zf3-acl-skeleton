<?php

/**
 * Class TranslationSaver
 *
 * @package     Translate\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Service\Factory;

use Interop\Container\ContainerInterface;
use Translate\Service\TranslationSaver;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 *
 * @package     Translate\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationSaverFactory {

    /**
     * This method creates the TranslationSaver service and returns its instance.
     * and passes array of translator file(s) locations which must be set in each module's module.config 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return TranslationSaver
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        $config = $container->get('config');
        $filePatterns = [];
        if (array_key_exists('translator', $config) && array_key_exists('translation_file_patterns', $config['translator'])) {
            $filePatterns = $config['translator']['translation_file_patterns'];
        }
        return new TranslationSaver($filePatterns);
    }

}
