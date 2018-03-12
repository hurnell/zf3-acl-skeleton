<?php

/**
 * Class SocialControllerFactory 
 *
 * @package     Social\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Controller\Factory;

use Zend\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Social\Controller\SocialController;
use Social\Service\SocialManager;

/**
 * This is the factory for SocialController. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 *
 * @package     Social\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialControllerFactory implements FactoryInterface {

    /**
     * Create/instantiate SocialController object and inject SocialManager object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return SocialController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SocialController {
        $socialManager = $container->get(SocialManager::class);
        return new SocialController($socialManager);
    }

}
