<?php

/**
 * App1\Helper\Nav\Title
 *
 */

namespace App1\Helper\Nav;

use \App1\Views\Helpers\Bootstrap\Nav;
use \App1\Helper\Lang\IEntries;

class Title
{

    const TODO = 'TODOTITLE';
    const TITLES = [
        '/home/dashboard' => IEntries::__DASHBOARD,
        '/user/manage' => IEntries::__USER_ACOUNT_MANAGEMENT,
        '/user/register' => IEntries::__USERS_SIGN_UP,
        '/user/lostpassword' => IEntries::__LOST_PASSWORD,
        '/user/edit' => IEntries::__USER_EDIT,
        '/user/detail' => IEntries::__USER_DETAIL,
        '/user/changepassword' => IEntries::__CHANGE_PASSWORD,
        '/metro/lignes/search' => IEntries::__METRO_LINES_SEARCH,
        '/metro/lignes/manage' => IEntries::__METRO_LINES_MANAGEMENT,
        '/metro/stations/manage' => IEntries::__METRO_STATIONS_MANAGEMENT,
        '/lang/manage' => IEntries::__LANG,
        '/lang/import' => IEntries::__LANG_IMPORT,
        '/lang/export' => IEntries::__LANG_EXPORT,
        '/acl/manage' => IEntries::__ACL,
        '/probes/manage' => IEntries::__SENSORS,
        '/probesconfig/manage' => IEntries::__SENSORS_CONFIG,
        '/probes/volumes' => IEntries::__SENSORS_VOLUMES,
        '/probes/export' => IEntries::__SENSORS_EXPORT,
        '/crud/manage' => IEntries::__CRUD_MANAGEMENT,
        '/database/tablesmysql' => IEntries::__DATABASE_MYSQL,
        '/database/tablespgsql' => IEntries::__DATABASE_PGSQL,
        '/database/uploadcsv' => IEntries::__DATABASE_UPLOAD,
        '/database/importcsv' => IEntries::__DATABASE_IMPORT
    ];

    /**
     * get
     *
     * @param string $url
     * @return string
     */
    public static function get(string $url): string
    {
        return self::transMark($url);
    }

    /**
     * transMark
     *
     * @param string $key
     * @return string
     */
    private static function transMark(string $key): string
    {
        if (isset(self::TITLES[$key])) {
            return Nav::transMark(self::TITLES[$key]);
        }
        return self::TODO;
    }
}
