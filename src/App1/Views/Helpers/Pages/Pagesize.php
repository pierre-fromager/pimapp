<?php

/**
 * App1\Views\Helpers\Pages\Pagesize
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Pages;

class Pagesize
{
    protected static $pagesizes = array(10, 25, 50, 75, 100);

    /**
     * get
     *
     * @param int $code
     * @return string
     */
    public static function get($code)
    {
        return self::$pagesizes[$code];
    }
    
    /**
     * getData
     *
     * @return array
     */
    public static function getData()
    {
        return self::$pagesizes;
    }
    
    /**
     * getCombo
     *
     * @param string $curent
     * @return string
     */
    public static function getCombo($url, $curent)
    {
        $pageSizes = array_combine(self::getData(), self::getData());
        $selector = \App1\Views\Helpers\Urlselector::get(
            'pageSize',
            $url,
            $pageSizes,
            $curent
        );

        $result = (string) $selector;
        return $result;
    }
}
