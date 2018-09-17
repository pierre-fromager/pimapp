<?php
/**
 * Description of Lib_Form_Csrf
 *
 * @author pierrefromager
 */
namespace App1\Form;

use \Pimvc\Tools\Session as toolsSession;

class Csrf
{
    const PREFIX = '';
    const CSRF_ERROR_MISSING_SESSION = 'Missing CSRF session token.';
    const CSRF_ERROR_MISSING_FORM = 'Missing CSRF form token.';
    const CSRF_ERROR_BAD_ORIGIN = 'Form origin does not match token origin.';
    const CSRF_ERROR_EXPIRED = 'CSRF token has expired.';
    const CSRF_ERROR_BAD_VALUE = 'Invalid CSRF token.';
    const CSRF_RANDOM_STRING = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijqlmnopqrtsuvwxyz0123456789';
    const CSRF_RANDOM_LENGTH = 13;
    const CSRF_DEFAULT_CHECK_ORIGIN = false;
    const CSRF_TIME_LENGTH = 10;

    protected static $doOriginCheck = self::CSRF_DEFAULT_CHECK_ORIGIN;
    protected static $errors = array();

    /**
     * check
     *
     * @param type $key
     * @param type $payload
     * @param type $timespan
     * @return boolean
     */
    public static function check($key, $payload, $timespan = null)
    {
        $tonkenName = self::PREFIX . $key;
        $hasToken = \Pimvc\Tools\Session::has($tonkenName);
        if ($hasToken === false) {
            self::addError(self::CSRF_ERROR_MISSING_SESSION);
            return false;
        }

        $hasPayload = (isset($payload[$key]));
        if ($hasPayload === false) {
            self::addError(self::CSRF_ERROR_MISSING_FORM);
            return false;
        }

        $hash = toolsSession::get(self::PREFIX . $key);
        if (self::isExpired($hash, $timespan) === false) {
            self::addError(self::CSRF_ERROR_EXPIRED);
            return false;
        }

        if (self::isValidOrigin($hash) === false) {
            self::addError(self::CSRF_ERROR_BAD_ORIGIN);
            return false;
        }

        return true;
    }

    /**
     * enableOriginCheck
     *
     * set Check Origin Option
     */
    public static function enableOriginCheck()
    {
        self::$doOriginCheck = true;
    }

    /**
     * generate
     *
     * @param string $key
     * @param boolean $originCheck
     * @return string
     */
    public static function generate($key, $originCheck)
    {
        $extra = ($originCheck === true) ? self::getChallengeOrigin() : '';
        $token = self::getToken($extra);
        toolsSession::set(self::PREFIX . $key, $token);
        return $token;
    }

    /**
     * Generates a random string of given $length.
     *
     * @param Integer $length The string length.
     * @return String The randomly generated string.
     */
    private static function randomString($length)
    {
        $string = '';
        $seed = self::CSRF_RANDOM_STRING;
        $max = strlen($seed) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $string .= $seed{intval(mt_rand(0.0, $max))};
        }
        return $string;
    }

    /**
     * addError
     *
     * @param string $errorString
     */
    private static function addError($errorString)
    {
        self::$errors[] = $errorString;
    }

    /**
     * getErrors
     *
     * @return array
     */
    public static function getErrors()
    {
        return self::$errors;
    }

    /**
     * getChallengeOrigin
     *
     * @return string
     */
    private static function getChallengeOrigin()
    {
        $server = $_SERVER['REMOTE_ADDR'];
        $agent = $_SERVER['HTTP_USER_AGENT'];
        return sha1($server . $agent);
    }

    /**
     * getToken
     *
     * @param string $extra
     * @return string
     */
    private static function getToken($extra)
    {
        $token = self::getTs()
            . $extra
            . self::randomString(self::CSRF_RANDOM_LENGTH);
        return base64_encode($token);
    }

    /**
     * validOrigin
     *
     * @param string $hash
     * @return boolean
     */
    private static function isValidOrigin($hash)
    {
        $isValid = (self::$doOriginCheck == false) ? true : self::isValidChallengeOrigin($hash);
        return $isValid;
    }

    /**
     * validChallengeOrigin
     *
     * @param string $hash
     * @return boolean
     */
    private static function isValidChallengeOrigin($hash)
    {
        $hashDecoded = base64_decode($hash);
        $originFromHash = substr($hashDecoded, self::CSRF_TIME_LENGTH, 40);
        $challengeOrigin = self::getChallengeOrigin();
        return ($challengeOrigin === $originFromHash);
    }

    /**
     * isExpired
     *
     * @param string $hash
     * @param string $timespan
     * @return boolean
     */
    private static function isExpired($hash, $timespan)
    {
        $isReady = ($timespan != null && is_int($timespan));
        $isExpired = true;
        if ($isReady) {
            $hashTime = substr(
                base64_decode($hash),
                0,
                self::CSRF_TIME_LENGTH
            );
            $delta = self::getTs() - $hashTime;
            $isExpired = ($delta > $timespan);
        }
        return $isExpired;
    }

    /**
     * getTs
     *
     * @return int
     */
    private static function getTs()
    {
        $ts = (int) floor(microtime(true));
        return $ts;
    }
}
