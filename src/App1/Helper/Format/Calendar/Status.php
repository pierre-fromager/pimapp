<?php
/**
 * Description of App1\Helper\Format\Calendar\Status
 *
 * @author pierrefromager
 */
namespace App1\Helper\Format\Calendar;

use \Pimvc\Helper\Format\Interfaces\Liste as listeInterface;

class Status implements Interfaces\Status, listeInterface
{
    protected static $status = array(
        self::CODE_WAITING => self::LABEL_WAITING,
        self::CODE_APPROVED => self::LABEL_APPROVED,
        self::CODE_REFUSED => self::LABEL_REFUSED,
        self::CODE_BILLED => self::LABEL_BILLED,
    );

    /**
     * get
     *
     * @param int $code
     * @return string
     */
    public static function get($code)
    {
        return self::$status[$code];
    }

    /**
     * getStatic
     *
     * @param int $code
     * @return string
     */
    public static function getStatic($code)
    {
        return self::get($code);
    }

    /**
     * getList
     *
     * @return array
     */
    public static function getList()
    {
        return self::$status;
    }
}
