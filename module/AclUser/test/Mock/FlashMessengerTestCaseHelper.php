<?php

/**
 * Get/add to application's configuration settings FlashMessengerTestCaseHelper
 *
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.0.0.1
 * @license     BSD
 * @uses        Zend Framework 3
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mock;

class FlashMessengerTestCaseHelper
{

    /**
     *
     * @var \Zend\Mvc\Plugin\FlashMessenger\FlashMessenger 
     */
    protected $flashMessenger;
    protected $test;
    protected $messages = [];

    public function __construct($test)
    {
        $this->test = $test;
        $this->flashMessenger = $test->getApplicationServiceLocator()->get('ControllerPluginManager')->get('FlashMessenger');
    }

    protected function updateMessages()
    {
        $container = $this->flashMessenger->getContainer();
        foreach ($container as $namespace => $messages) {
            $this->messages[$namespace] = $messages;
        }
    }

    public function assertFlashMessengerHasNamespace($namespace, $failureMessage = '')
    {
        $this->updateMessages();
        $namespaces = implode('", "', array_keys($this->messages));

        if (!array_key_exists($namespace, $this->messages)) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $line = $caller['line'];
            $failureMessage = $failureMessage === '' ? 'No messages found for namespace: "' 
                    . $namespace . '". The following namespaces do exist: "' 
                    . $namespaces . '".' . "\n assertFlashMessengerHasNamespace called on line: " 
                    . $line : $failureMessage;
        }
        $this->test->assertArrayHasKey($namespace, $this->messages, $failureMessage);
    }

    public function assertFlashMessengerHasMessage($namespace, $match, $failureMessage = '')
    {
        $this->updateMessages();
        if (!array_key_exists($namespace, $this->messages)) {
            throw new \Exception('Namespace ' . $namespace . ' is not set');
        }
        $messages = ($this->messages[$namespace])->toArray();
        if (!in_array($match, $messages)) {
            $bt = debug_backtrace();
            $caller = array_shift($bt);
            $line = $caller['line'];
            $namespaces = implode('", "', array_keys($this->messages));
            $allMessages = implode('", "', $messages);
            $failureMessage = $failureMessage === '' ? 'Message "' . $match
                    . '" in namespace "' . $namespace . '" was not found. The following messages "'
                    . $allMessages . '" with namespaces "' . $namespaces . '" do exist."' 
                    . "\n assertFlashMessengerHasNamespace called on line: " 
                    . $line : $failureMessage;
        }

        $this->test->assertContains($match, $messages, $failureMessage);
    }

}
