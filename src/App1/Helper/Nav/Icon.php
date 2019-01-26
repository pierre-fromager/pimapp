<?php

/**
 * App1\Helper\Nav\Icon
 *
 */

namespace App1\Helper\Nav;

use \Pimvc\Views\Helpers\Fa;

class Icon
{

    const ICONS = [
        '/log/index' => Fa::BOOK,
        '/home/dashboard' => Fa::DASHBOARD,
        '/home/cgu' => Fa::NEWSPAPER_O,
        '/user/manage' => Fa::USER,
        '/user/edit' => Fa::USER,
        '/user/detail' => Fa::USER,
        '/user/login' => Fa::SIGN_IN,
        '/user/logout' => Fa::SIGN_OUT,
        '/user/register' => Fa::CERTIFICATE,
        '/user/lostpassword' => Fa::LOCK,
        '/user/changepassword' => Fa::LOCK,
        '/lang/manage' => Fa::LANGUAGE,
        '/lang/import' => Fa::LANGUAGE,
        '/lang/export' => Fa::LANGUAGE,
        '/acl/manage' => Fa::LOCK,
        '/metro/lignes/manage' => Fa::TRAIN,
        '/metro/lignes/edit' => Fa::TRAIN,
        '/metro/lignes/detail' => Fa::TRAIN,
        '/metro/lignes/search' => Fa::TRAIN,
        '/metro/stations/manage' => Fa::TRAIN,
        '/metro/stations/edit' => Fa::TRAIN,
        '/metro/stations/detail' => Fa::TRAIN,
        '/probes/manage' => Fa::COMPASS,
        '/probes/edit' => Fa::COMPASS,
        '/probes/detail' => Fa::COMPASS,
        '/probes/volumes' => Fa::LINE_CHART,
        '/probes/export' => Fa::CLOUD_DOWNLOAD,
        '/probesconfig/manage' => Fa::COMPASS,
        '/probesconfig/edit' => Fa::COMPASS,
        '/probesconfig/detail' => Fa::COMPASS,
        '/crud/manage' => Fa::CUBE,
        '/crud/edit' => Fa::PENCIL,
        '/crud/detail' => Fa::EYE,
        '/database/tablesmysql' => Fa::DATABASE,
        '/database/tablespgsql' => Fa::DATABASE,
        '/database/tables4d' => Fa::DATABASE,
        '/database/uploadcsv' => Fa::CLOUD_UPLOAD,
        '/database/importcsv' => Fa::CLOUD_DOWNLOAD
    ];

    /**
     * get
     *
     * @param string $url
     * @return string
     */
    public static function get(string $url): string
    {
        $key = (isset(self::ICONS[$url])) ? self::ICONS[$url] : Fa::QUESTION;
        return self::getIcon($key);
    }

    /**
     * getIcon
     *
     * @param string $key
     * @return string
     */
    private static function getIcon(string $key): string
    {
        return Fa::getFontClass($key);
    }
}
