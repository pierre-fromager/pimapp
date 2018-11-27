<?php

/**
 * App1\Helper\Nav\Title
 *
 */

namespace App1\Helper\Nav;

use App1\Views\Helpers\Bootstrap\Nav;
use \App1\Helper\Lang\IEntries as ILang;

class Title
{

    /**
     * get
     *
     * @param string $url
     * @return string
     */
    public static function get(string $url): string
    {
        if (strpos($url, 'home/index') !== false) {
            return Nav::transMark(ILang::__HOME);
        } elseif (strpos($url, 'user/manage') !== false) {
            return Nav::transMark(ILang::__USER_ACOUNT_MANAGEMENT);
        } elseif (strpos($url, 'user/register') !== false) {
            return Nav::transMark(ILang::__USERS_SIGN_UP);
        } elseif (strpos($url, 'user/lostpassword') !== false) {
            return Nav::transMark(ILang::__LOST_PASSWORD);
        } elseif (strpos($url, 'user/changepassword') !== false) {
            return Nav::transMark(ILang::__CHANGE_PASSWORD);
        } elseif (strpos($url, '/lignes/search') !== false) {
            return Nav::transMark(ILang::__METRO_LINES_SEARCH);
        } elseif (strpos($url, '/lignes/manage') !== false) {
            return Nav::transMark(ILang::__METRO_LINES_MANAGEMENT);
        } elseif (strpos($url, '/stations/manage') !== false) {
            return Nav::transMark(ILang::__METRO_STATIONS_MANAGEMENT);
        } elseif (strpos($url, 'lang/') !== false) {
            return Nav::transMark(ILang::__LANG);
        } elseif (strpos($url, 'acl/manage') !== false) {
            return Nav::transMark(ILang::__ACL);
        } elseif (strpos($url, 'probes/manage') !== false) {
            return Nav::transMark(ILang::__SENSORS);
        } elseif (strpos($url, 'probesconfig/manage') !== false) {
            return Nav::transMark(ILang::__SENSORS_CONFIG);
        } elseif (strpos($url, 'crud/manage') !== false) {
            return Nav::transMark(ILang::__CRUD_MANAGEMENT);
        }
        return 'TODOTITLE';
    }
}
