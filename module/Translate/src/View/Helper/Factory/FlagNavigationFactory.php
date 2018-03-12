<?php

/**
 * Class FlagNavigationFactory
 *
 * @package     Translate\View\Helper\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
namespace Translate\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Translate\View\Helper\FlagNavigation;

/**
 * This is the factory for Menu view helper. Its purpose is to instantiate the
 * helper and init menu items.
 *
 * @package     Translate\View\Helper\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class FlagNavigationFactory implements FactoryInterface
{

    /**
     * Create/instantiate FlagNavigation object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return FlagNavigation
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null):FlagNavigation
    {
        $languageManager = $container->get('languageManager');
        return new FlagNavigation($languageManager);
    }

}
