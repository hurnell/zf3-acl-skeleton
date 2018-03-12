<?php

/**
 * Creates a logger for the application with stream logging and firephp logging
 * 
 * @package Application  
 * @subpackage Log
 * @copyright Copyright (c) 2011-14 N.Hurnell
 * @license BSD
 * @version 1.0.0.01
 * @uses Zend Framework 2.2.5
 */

namespace Application\Log;

use Zend\Log\Logger;
use Zend\Log\Writer\FirePhp;
use Zend\Log\Writer\ChromePhp;
use Zend\Log\Writer\Stream;
//@codeCoverageIgnoreStart
require_once './config/include/FirePHPCore/FirePHP.class.php';
require_once './config/include/ChromePHPCore/ChromePhp.php';
//@codeCoverageIgnoreEnd
/**
 * Creates a logger for the application with stream logging and firephp logging
 * 
 * @package Application  
 * @subpackage Log
 * @copyright Copyright (c) 2011-14 N.Hurnell
 * @license BSD
 * @version 1.0.0.01
 * @uses Zend Framework 2.2.5
 */
class Log
{

    /**
     * Instance of this class
     * @var Application\Log\Log 
     */
    protected static $_instance = NULL;

    /**
     * The logger that actually does the logging
     * @var Zend\Log\Logger
     */
    protected $_log = NULL;

    /**
     * +Static Method: getInstance()
     * {@source}
     * @return instance of this class
     */
    public static function getInstance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * +Method: getLog()
     * {@source}
     * @return instance of the logger class
     */
    public function getLog()
    {
        if (NULL === $this->_log) {
            $this->_log = new Logger();
            if ($this->notCommandLine()) {
                $fireWriter = new FirePhp();
                $chromeWriter = new ChromePhp();
            }
            if ($this->notCommandLine()) {
                $this->_log->addWriter($fireWriter);
                $this->_log->addWriter($chromeWriter);
            }
            $streamWriter = new Stream('./data/logs/skeleton.log');
            $this->_log->addWriter($streamWriter);
        }
        return $this->_log;
    }

    /**
     * +Method: info()
     * {@source}
     * Calls the info function directly
     * @param string $msg
     * @return null
     */
    public function info($msg)
    {
        $log = $this->getLog();
        $log->info($msg);
    }

    /**
     * Check whether application is called from command line
     * 
     * @return boolean whether application is NOT called from command line
     */
    public function notCommandLine()
    {
        return php_sapi_name() != 'cli';
    }

}
