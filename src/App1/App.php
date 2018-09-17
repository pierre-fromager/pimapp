<?php
/**
 * Description of App1\App
 *
 * @author pierrefromager
 */
namespace App1;

use \Pimvc\App as mainApp;

class App extends mainApp
{

    /**
     * __construct
     *
     * @param \Pimvc\Config $config
     */
    public function __construct(\Pimvc\Config $config)
    {
        parent::__construct($config);
        return $this;
    }
}
