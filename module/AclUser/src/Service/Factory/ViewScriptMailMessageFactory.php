<?php

/**
 * Class ViewScriptMailMessageFactory
 *
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUser\Mail\MailMessage;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

/**
 * This is the factory class for MailMessage service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 * 
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ViewScriptMailMessageFactory implements FactoryInterface
{

    /**
     * Create/instantiate MailMessage object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return MailMessage
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null):MailMessage
    {
        $renderer = $container->get('ViewRenderer');
        $mailMessage = new MailMessage($renderer);
        $config = $container->get('config');
        if (array_key_exists('smtp_options', $config)) {
            $transport = new SmtpTransport();
            $options = new SmtpOptions($config['smtp_options']);
            $transport->setOptions($options);
            $mailMessage->setSmtpTransport($transport);
        }
        return $mailMessage;
    }

}
