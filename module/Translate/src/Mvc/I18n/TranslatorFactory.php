<?php

/**
 * Class TranslatorFactory
 *
 * @package     Translate\Mvc\I18n
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Mvc\I18n;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Translate\I18n\Translator\Translator as I18nTranslator;
use Zend\Mvc\I18n\Translator as MvcTranslator;
use Translate\Service\TranslationSaver;

/**
 * Class TranslatorFactory used to instantiate MvcTranslator
 *
 * @package     Translate\Mvc\I18n
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslatorFactory implements FactoryInterface
{

    /**
     * This is the factory for MvcTranslator. Its purpose is to instantiate 
     * MvcTranslator and inject dependencies into it.
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return MvcTranslator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        // Create translator from configuration
        $i18nTranslator = I18nTranslator::factory($config['translator']);
        if (array_key_exists('locale', $config['translator'])) {
            $i18nTranslator->setFallbackLocale($config['translator']['locale']);
        }
        /**
         * Inject TranslatorPluginManager, if you need to use same 
         * if ($container->has('TranslatorPluginManager')) {
         * $i18nTranslator->setPluginManager($container->get('TranslatorPluginManager'));
         * }
         * */
        $i18nTranslator->setTranslationSaver($container->get(TranslationSaver::class));
        return new MvcTranslator($i18nTranslator);
    }

}
