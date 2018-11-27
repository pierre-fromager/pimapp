<?php


/**
 * App1\Helper\Nav\Icon
 *
 */

namespace App1\Helper\Nav;

use Pimvc\Views\Helpers\Fa as faHelper;

class Icon
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
            return faHelper::getFontClass(faHelper::HOME);
        } elseif (strpos($url, '/metro', 0) !== false) {
            return faHelper::getFontClass(faHelper::TRAIN);
        } elseif (strpos($url, 'user/manage') !== false) {
            return faHelper::getFontClass(faHelper::USER);
        } elseif (strpos($url, '/search') !== false) {
            return faHelper::getFontClass(faHelper::SEARCH);
        } elseif (strpos($url, '/login') !== false) {
            return faHelper::getFontClass(faHelper::SIGN_IN);
        } elseif (strpos($url, '/logout') !== false) {
            return faHelper::getFontClass(faHelper::SIGN_OUT);
        } elseif (strpos($url, 'lang/') !== false) {
            return faHelper::getFontClass(faHelper::LANGUAGE);
        } elseif (strpos($url, 'password') !== false) {
            return faHelper::getFontClass(faHelper::LOCK);
        } elseif (strpos($url, 'register') !== false) {
            return faHelper::getFontClass(faHelper::CERTIFICATE);
        } elseif (strpos($url, 'database') !== false) {
            return faHelper::getFontClass(faHelper::DATABASE);
        } elseif (strpos($url, 'acl') !== false) {
            return faHelper::getFontClass(faHelper::DATABASE);
        } elseif (strpos($url, 'probes/') !== false) {
            return faHelper::getFontClass(faHelper::COMPASS);
        } elseif (strpos($url, 'probesconfig/') !== false) {
            return faHelper::getFontClass(faHelper::COG);
        } elseif (strpos($url, 'crud/manage') !== false) {
            return faHelper::getFontClass(faHelper::COG);
        }
        return faHelper::getFontClass(faHelper::QUESTION);
    }
}
