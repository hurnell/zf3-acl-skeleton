<?php

/**
 * Class AuthAdapterServiceTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;

/**
 * Test various aspects of AclUser\Service\AuthAdapter
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class LogTest extends AbstractHttpControllerTestCase
{

    protected $builder;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ServiceMockBuilder::getConfig());
        parent::setUp();
    }

    public function testLogWritesToStream()
    {
        $log = \Application\Log\Log::getInstance();
        $message = 'THIS IS A TEST MESSAGE: ' . \Zend\Math\Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*', true) . time();
        $log->info($message);
        $contents = $this->tail('./data/logs/skeleton.log', 5);
        $this->assertTrue(strpos($contents, $message) !== false, 'message was not found in log');
    }

    public function testOddLog()
    {
        $this->expectException(\Exception::class);
        $mock = $this->getMockBuilder(\Application\Log\Log::class)->setMethods(['notCommandLine'])->disableOriginalConstructor()->getMock();
        $mock->expects($this->any())
                ->method('notCommandLine')
                ->willReturn(true);
        $message = 'THIS IS A TEST MESSAGE: ' . \Zend\Math\Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*', true) . time();
        $mock->info($message);
    }

    public function tail($filename, $lines = 10, $buffer = 4096)
    {
        // Open the file
        $f = fopen($filename, "rb");

        // Jump to last character
        fseek($f, -1, SEEK_END);

        // Read it and adjust line number if necessary
        // (Otherwise the result would be wrong if file doesn't end with a blank line)
        if (fread($f, 1) != "\n")
            $lines -= 1;

        // Start reading
        $output = '';
        $chunk = '';

        // While we would like more
        while (ftell($f) > 0 && $lines >= 0) {
            // Figure out how far back we should jump
            $seek = min(ftell($f), $buffer);

            // Do the jump (backwards, relative to where we are)
            fseek($f, -$seek, SEEK_CUR);

            // Read a chunk and prepend it to our output
            $output = ($chunk = fread($f, $seek)) . $output;

            // Jump back to where we started reading
            fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);

            // Decrease our line counter
            $lines -= substr_count($chunk, "\n");
        }

        // While we have too many lines
        // (Because of buffer size we might have read too many)
        while ($lines++ < 0) {
            // Find first newline and remove all text before that
            $output = substr($output, strpos($output, "\n") + 1);
        }

        // Close file and return
        fclose($f);
        return $output;
    }

}
