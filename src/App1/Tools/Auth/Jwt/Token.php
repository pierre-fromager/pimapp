<?php

/**
 * Description of App1\Tools\Auth\Jwt
 *
 * @author pierrefromager
 */

namespace App1\Tools\Auth\Jwt;

use Firebase\JWT\JWT as Fjwt;

class Token implements Interfaces\Token
{
    private static $issueAt;
    private static $ttl;
    private static $issueAtDelay;

    /**
     * encode
     *
     * @param string $id
     * @param string $login
     * @param string $password
     * @return string
     */
    public static function encode($id, $login, $password)
    {
        $tokenId = base64_encode(openssl_random_pseudo_bytes(self::TOKEN_RANDOM_BYTES_LEN));
        $issuedAt = time();
        $notBefore = $issuedAt + self::$issueAtDelay;  //Adding 10 seconds
        $expire = $notBefore + self::$ttl; // Adding 60 seconds
        $appInstance = \Pimvc\App::getInstance();
        $serverName = $appInstance->getRequest()->getHost();
        $data = [
            self::TOKEN_IAT => $issuedAt, // Issued at: time when the token was generated
            self::TOKEN_JTI => $tokenId, // Json Token Id: an unique identifier for the token
            self::TOKEN_ISS => $serverName, // Issuer
            self::TOKEN_NBF => $notBefore, // Not before
            self::TOKEN_EXP => $expire, // Expire
            self::TOKEN_DATA => [// Data related to the signer user
                self::TOKEN_DATA_ID => $id, // userid from the users table
                self::TOKEN_DATA_LOGIN => $login, // User name
                self::TOKEN_DATA_PASSWORD_HASH => password_hash($password, PASSWORD_DEFAULT),
                self::TOKEN_DATA_IAT_S => strftime('%c', $issuedAt),
                self::TOKEN_DATA_NBF_S => strftime('%c', $notBefore),
                self::TOKEN_DATA_EXP_S => strftime('%c', $expire), // Expire
            ]
        ];
        $jwtConfig = self::getConfig();
        return Fjwt::encode(
            $data,
            $jwtConfig[self::TOKEN_SECRET],
            $jwtConfig[self::TOKEN_ALGO]
        );
    }

    /**
     * decode
     *
     * @param string $token
     * @return mixed
     */
    public static function decode($token = '')
    {
        $jwtConfig = self::getConfig();
        return Fjwt::decode(
            $token,
            $jwtConfig[self::TOKEN_SECRET],
            [$jwtConfig[self::TOKEN_ALGO]]
        );
    }

    /**
     * init
     */
    public static function init()
    {
        self::setIssueAt();
        self::setIssueAtDelay();
        self::setTtl();
    }

    /**
     * setIssueAt
     *
     * @param string $dateTime
     */
    public static function setIssueAt($dateTime = '')
    {
        self::$issueAt = ($dateTime) ? $dateTime : time();
    }

    /**
     * setIssueAtDelay
     *
     * @param int $delay
     */
    public static function setIssueAtDelay($delay = '')
    {
        self::$issueAtDelay = ($delay !== '') ? $delay : self::TOKEN_ISSUE_AT_DELAY;
    }

    /**
     * setTtl
     *
     * @param int $ttl
     */
    public static function setTtl($ttl = '')
    {
        self::$ttl = ($ttl) ? $ttl : self::TOKEN_TTL;
    }

    /**
     * getConfig
     *
     * @return array
     */
    private static function getConfig()
    {
        return \Pimvc\App::getInstance()->getConfig()->getSettings(
            self::TOKEN_CONFIG_KEY
        );
    }
}
