<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Nav
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

class Nav extends \Pimvc\View
{

    const _TEMPLATE = __DIR__ . '/Template/Nav.php';

    /**
     * __construct
     *
     * @return $this
     */
    public function __construct()
    {
        $this->setFilename(self::_TEMPLATE);
        parent::__construct();
        return $this;
    }
}
