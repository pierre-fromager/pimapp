<?php
/**
 * App1\Middleware\Restfull
 *
 * is a request url rewriter to match action controller from request method
 * according to restfull base requirements
 */
namespace App1\Middleware;

use Pimvc\Http\Response as PimResponse;
use Pimvc\Tools\User\Auth as authTools;
use App1\Tools\Auth\Jwt\Token;

class Jwt implements \Pimvc\Http\Interfaces\Layer
{
    const JWT_DEBUG = true;
    const JWT_PASSWORD = 'password';
    const JWT_USER_STATUS = 'status';
    const JWT_STATUS_VALID = 'valid';
    const JWT_AUTORIZATION = 'Authorization';
    const JWT_URI_PREFIX = '/api/v1/';
    const JWT_EP_AUTH = 'auth';
    const JWT_EP_AUTHSN = 'authsn';
    const JWT_ERROR = 'error';
    const JWT_ERROR_MESSAGE = 'errorMessage';

    private $request;
    private $headers;
    private $app;

    /**
     * peel
     *
     * @param object $object
     * @param \Closure $next
     * @return \Closure
     */
    public function peel($object, \Closure $next)
    {
        $this->process();
        return $next($object);
    }

    /**
     * process
     *
     */
    private function process()
    {
        $this->app = \Pimvc\App::getInstance();
        $this->request = $this->app->getRequest();
        $this->headers = $this->request->getHeaders();
        if ($this->required()) {
            if ($this->isValidAuthorization()) {
                try {
                    $authorization = $this->headers[self::JWT_AUTORIZATION];
                    list($bearer, $token) = explode(' ', $authorization);
                    $decodedToken = Token::decode($token);
                    if (isset($decodedToken->{Token::TOKEN_DATA}->{Token::TOKEN_DATA_ID})) {
                        $userId = $decodedToken->{Token::TOKEN_DATA}->{Token::TOKEN_DATA_ID};
                        $user = $this->getUser($userId);
                        if ($user !== false) {
                            if ($this->isValidCredential($decodedToken, $user)) {
                                new authTools(
                                    $user[Token::TOKEN_DATA_LOGIN],
                                    $user[self::JWT_PASSWORD]
                                );
                            } else {
                                $this->dispatchError(403, PimResponse::HTTP_CODES[403]);
                            }
                        } else {
                            $this->dispatchError(403, PimResponse::HTTP_CODES[403]);
                        }
                    } else {
                        $this->dispatchError(403, PimResponse::HTTP_CODES[403]);
                    }
                } catch (\Exception $e) {
                    $this->dispatchError(500, $e->getMessage());
                }
            } else {
                if (!$this->isPreflight()) {
                    $this->dispatchError(401, 'Bad request : Missing ' . self::JWT_AUTORIZATION);
                }
            }
        }
    }
    
    /**
     * isPreflight
     *
     * @return bool
     */
    private function isPreflight(): bool
    {
        $isOptionsMethod = ($this->request->getMethod() == 'OPTIONS');
        $corsHeadersKeys = array_keys($this->headers);
        $hasOrigin = in_array('Origin', $corsHeadersKeys);
        $hasAccessControlRequestMethod = in_array(
            'Access-Control-Request-Method',
            $corsHeadersKeys
        );
        return ($isOptionsMethod && $hasOrigin && $hasAccessControlRequestMethod);
    }

    /**
     * isValidCredential
     *
     * @param object $decodedToken
     * @param array $user
     * @return boolean
     */
    private function isValidCredential($decodedToken, $user): bool
    {
        $login = $decodedToken->{Token::TOKEN_DATA}->{Token::TOKEN_DATA_LOGIN};
        $passwordHash = $decodedToken->{Token::TOKEN_DATA}->{Token::TOKEN_DATA_PASSWORD_HASH};
        $checkLogin = ($login === $user[Token::TOKEN_DATA_LOGIN]);
        $checkPassword = password_verify($user[self::JWT_PASSWORD], $passwordHash);
        $checkStatus = ($user[self::JWT_USER_STATUS] === self::JWT_STATUS_VALID);
        return ($checkLogin && $checkPassword && $checkStatus);
    }

    /**
     * getUser
     *
     * @param int $userId
     * @return array | false
     */
    private function getUser($userId)
    {
        $authModel = new \App1\Model\Users($this->app->getConfig()->getSettings('dbPool'));
        $r = $authModel->find([], [Token::TOKEN_DATA_ID => $userId])->getRowsetAsArray();
        return isset($r[0]) ? $r[0] : false;
    }

    /**
     * dispatchError
     *
     * @param int $httpCode
     * @param string $errorMessage
     */
    private function dispatchError($httpCode, $errorMessage)
    {
        $resp = $this->app->getResponse();
        $resp->setType($resp::TYPE_JSON)->setHttpCode($httpCode)->setContent([
            self::JWT_ERROR => true,
            self::JWT_ERROR_MESSAGE => $errorMessage
        ]);
        $resp->dispatch($andDie = true);
    }

    /**
     * isValidAuthorization
     *
     * @return boolean
     */
    private function isValidAuthorization()
    {
        return (
            isset($this->headers[self::JWT_AUTORIZATION]) && $this->headers[self::JWT_AUTORIZATION] != ''
            );
    }

    /**
     * required
     *
     * @return boolean
     */
    private function required()
    {
        return (boolean) (
            !$this->isEpAuth() && $this->uriPrefix() === self::JWT_URI_PREFIX
            );
    }

    /**
     * isEpAuth
     *
     * @return boolean
     */
    private function isEpAuth()
    {
        $disallowed = [self::JWT_EP_AUTH, self::JWT_EP_AUTHSN];
        for ($c = 0; $c < count($disallowed); ++$c) {
            $composed = $this->uriPrefix() . $disallowed[$c];
            $isAuth = ($composed == $this->app->getRequest()->getUri());
            if ($isAuth) {
                return true;
            }
        }
        return false;
    }

    /**
     * uriPrefix
     *
     * @return string
     */
    private function uriPrefix()
    {
        return substr(
            $this->app->getRequest()->getUri(),
            0,
            strlen(self::JWT_URI_PREFIX)
        );
    }

    /**
     * log
     *
     */
    private function log($data = [])
    {
        $this->app->getLogger()->log(
            __CLASS__,
            \Pimvc\Logger::DEBUG,
            $data
        );
    }
}
