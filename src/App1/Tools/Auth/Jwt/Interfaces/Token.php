<?php
/**
 * Description of App1\Tools\Auth\Jwt\Interfaces\Token
 *
 * @author pierrefromager
 */
namespace App1\Tools\Auth\Jwt\Interfaces;

interface Token
{
    const TOKEN_SECRET = 'secret';
    const TOKEN_ALGO = 'algorithm';
    const TOKEN_CONFIG_KEY = 'jwt';
    const TOKEN_IAT = 'iat';
    const TOKEN_JTI = 'jti';
    const TOKEN_ISS = 'iss';
    const TOKEN_NBF = 'nbf';
    const TOKEN_EXP = 'exp';
    const TOKEN_DATA = 'data';
    const TOKEN_DATA_ID = 'id';
    const TOKEN_DATA_LOGIN = 'login';
    const TOKEN_DATA_PASSWORD_HASH = 'password_hash';
    const TOKEN_DATA_IAT_S = 'iat_s';
    const TOKEN_DATA_NBF_S = 'nbf_s';
    const TOKEN_DATA_EXP_S = 'exp_s';
    const TOKEN_RANDOM_BYTES_LEN = 32;
    const TOKEN_ISSUE_AT_DELAY = 10; // secs
    const TOKEN_TTL = 60; // secs

    public static function encode($id, $login, $password);

    public static function decode($token = '');

    public static function init();

    public static function setIssueAt($dateTime = '');

    public static function setIssueAtDelay($delay = '');

    public static function setTtl($ttl = '');
}
