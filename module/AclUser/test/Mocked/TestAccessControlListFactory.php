<?php

/**
 * Class TestAccessControlListFactory 
 *
 * @package     TranslateTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mocked;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUserTest\Mocked\TestAccessControlList;

/**
 * The factory responsible for creating of TestAccessControlList object.
 *
 * @package     AclUserTest\Mocked
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class TestAccessControlListFactory implements FactoryInterface
{

    /**
     * Create/instantiate TestAccessControlList 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return TestAccessControlList
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): TestAccessControlList
    {
        return new TestAccessControlList();
    }

}
