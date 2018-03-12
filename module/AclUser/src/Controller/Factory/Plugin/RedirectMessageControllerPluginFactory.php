<?php

/**
 * Class RedirectMessageControllerPluginFactory
 *
 * @package     Application\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Controller\Factory\Plugin;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin;

/**
 * This is the factory for RedirectMessagePlugin. Its purpose is to instantiate the
 * controller plugin.
 * 
 * 
 *
 * @package     Application\Controller\Factory\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class RedirectMessageControllerPluginFactory implements FactoryInterface {

    /**
     * Create/instantiate RedirectMessagePlugin object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return RawResponsePlugin
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RedirectMessagePlugin {
        return new RedirectMessagePlugin();
    }

}
