<?php

/**
 * Class RawResponseControllerPluginFactory
 *
 * @package     Application\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Application\Controller\Factory\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Application\Mvc\Controller\Plugin\RawResponsePlugin;

/**
 * This is the factory for RawResponsePlugin. Its purpose is to instantiate the
 * controller plugin.
 *
 * @package     Application\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class RawResponseControllerPluginFactory implements FactoryInterface
{

    /**
     * Create/instantiate RawResponsePlugin object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return RawResponsePlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RawResponsePlugin
    {
        return new RawResponsePlugin();
    }

}
