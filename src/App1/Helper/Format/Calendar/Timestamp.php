<?php
/**
 * Description of App1\Helper\Format\Calendar\Timestamp
 *
 * convert Timestamp to simple Date format
 *
 * @author pierrefromager
 */
namespace App1\Helper\Format\Calendar;

use \Pimvc\Helper\Format\Interfaces\Liste as listeInterface;

class Timestamp implements listeInterface
{
    const TIMESTAMP_START = 0;
    const TIMESTAMP_END = 10;

    /**
     * getStatic
     *
     * @param int $timestamp
     * @return string
     */
    public static function getStatic($timestamp)
    {
        return substr($timestamp, self::TIMESTAMP_START, self::TIMESTAMP_END);
    }
}
