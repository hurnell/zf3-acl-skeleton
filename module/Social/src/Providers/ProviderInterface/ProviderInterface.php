<?php

/**
 * Class ProviderInterface 
 *
 * @package     Social\Providers\ProviderInterface
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Providers\ProviderInterface;

/**
 * Interface ProviderInterface 
 * ensures that basic methods are available to all providers
 *
 * @package     Social\Providers\ProviderInterface
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
interface ProviderInterface {

    /**
     * Get the full redirect URL (including query string)
     * 
     * @param string $callback
     */
    public function getRedirectRoute($callback);

    /**
     * Send Client Request
     * 
     * @param string $callback
     * @param array $queryParams
     */
    public function sendClientRequest($callback, $queryParams);
}
