<?php

/**
 * Description of nav
 *
 * @author pierrefromager
 */
namespace App1\Views\Helpers\Bootstrap;

class Nav extends \Pimvc\View
{
    const TEMPLATE_PATH = '/Template/';
    const TEMPLATE_PARTIAL = 'Nav.php';
    
    /**
     * __construct
     *
     * @return $this
     */
    public function __construct()
    {
        $this->setFilename($this->getPartialFilename());
        parent::__construct();
        return $this;
    }
    
    /**
     * getPartialFilename
     *
     * @return string
     */
    private function getPartialFilename()
    {
        return __DIR__ . self::TEMPLATE_PATH . self::TEMPLATE_PARTIAL;
    }
}
