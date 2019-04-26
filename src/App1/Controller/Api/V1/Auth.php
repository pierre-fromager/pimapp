<?php
/**
 * Description of App1\Controller\Api\V1\Auth
 *
 * @author Pierre Fromager
 */
namespace App1\Controller\Api\V1;

use Pimvc\Input\Filter as inputFilter;
use Pimvc\Controller\Response as RestfulController;
use \App1\Model\Users as modelUser;
use App1\Tools\Auth\Jwt\Token as JwtToken;

class Auth extends RestfulController
{
    const AUTH_ERROR = 'error';
    const AUTH_ERROR_MESSAGE = 'errorMessage';
    const AUTH_LOGIN = 'login';
    const AUTH_PASSWORD = 'password';
    const AUTH_ALLOWED_METHOD = ['POST','OPTIONS'];

    private $request;
    private $baseUrl;
    private $method;
    private $jwt;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->request = $this->getApp()->getInstance()->getRequest();
        $this->baseUrl = $this->request->getBaseUrl();
        $this->method = $this->request->getMethod();
        if (!in_array($this->method, self::AUTH_ALLOWED_METHOD)) {
            $this->dispatchError(400, 'Bad request : method not allowed');
        }
    }

    /**
     * // POST
     *
     * store
     *
     */
    final public function store()
    {
        $input = $this->getStoreFilter($this->getParams());
        $data = [
            self::AUTH_LOGIN => $input->{self::AUTH_LOGIN},
            self::AUTH_PASSWORD => $input->{self::AUTH_PASSWORD},
        ];
        if (!$this->getStoreValidator($data[self::AUTH_LOGIN], $data[self::AUTH_PASSWORD])) {
            $this->dispatchError(400, 'Bad request : missing params, login or password');
        }
        $user = $this->getUser($data[self::AUTH_LOGIN], $data[self::AUTH_PASSWORD]);
        if ($user) {
            if ($user['status'] != 'valid') {
                $this->dispatchError(403, 'Forbidden : invalid account');
            }
            $this->setJwt($data, $user);
            $data = ['token' => $this->jwt];
        } else {
            $this->dispatchError(403, 'Forbidden : incorrect credentials');
        }
        $this->getApp()->getResponse()->setHeaders($this->getCorsHeaders());
        return $this->wrappedResponse(__FUNCTION__, $data);
    }
    
    /**
     * // OPTIONS
     *
     * preflight CORS
     *
     */
    final public function preflight()
    {
        $this->getApp()->getResponse()->setHeaders($this->getCorsHeaders());
        return $this->wrappedResponse(__FUNCTION__, []);
    }
    
    /**
     * getCorsHeaders
     *
     * @return array
     */
    private function getCorsHeaders():array
    {
        $aca = 'Access-Control-Allow-';
        $headers[] = $aca . 'Origin: *';
        $headers[] = $aca . 'Credentials: true';
        $headers[] = $aca . 'Methods: GET, POST, PUT, DELETE, OPTIONS';
        $headers[] = $aca . 'Headers: Access-Control-Allow-Headers, Origin,Accept, X-Requested-With, Content-Type, Access-Control-Request-Method, Access-Control-Request-Headers, Access-Control-Allow-Origin';
        return $headers;
    }

    /**
     * setJwt
     *
     * @param array $data
     * @param array $user
     */
    private function setJwt($data, $user)
    {
        JwtToken::init();
        JwtToken::setIssueAt(time());
        JwtToken::setIssueAtDelay(0);
        JwtToken::setTtl(1200);
        $this->jwt = JwtToken::encode(
            $user['id'],
            $data[self::AUTH_LOGIN],
            $data[self::AUTH_PASSWORD]
        );
    }

    /**
     * getUser
     *
     * @param int $userId
     * @return array | false
     */
    private function getUser($login, $password)
    {
        $dbCongig = $this->getApp()->getConfig()->getSettings('dbPool');
        $authModel = new modelUser($dbCongig);
        $where = [self::AUTH_LOGIN => $login, self::AUTH_PASSWORD => $password];
        $r = $authModel->find([], $where)->getRowsetAsArray();
        return isset($r[0]) ? $r[0] : false;
    }

    /**
     * wrappedResponse
     *
     * @param type $action
     * @param type $data
     * @return Response
     */
    private function wrappedResponse($action, $data)
    {
        $payload = [
            'error' => false,
            'errorMessage' => '',
            'request' => [
                'router_uri' => $this->getApp()->getRouter()->getUri(),
                'request_uri' => $this->getApp()->getRequest()->getUri(),
                'method' => $this->getApp()->getRequest()->getMethod(),
                'params' => $this->getParams(),
            ],
            'process' => [
                'controller' => __CLASS__,
                'action' => $action,
            ],
            'data' => $data
        ];
        return $this->getJsonResponse($payload);
    }

    /**
     * getLoginInputFilter
     *
     * @return inputFilter
     */
    private function getStoreFilter($postedDatas)
    {
        return new inputFilter(
            $postedDatas,
            [
            self::AUTH_LOGIN => FILTER_SANITIZE_STRING,
            self::AUTH_PASSWORD => FILTER_SANITIZE_STRING,
            ]
        );
    }

    /**
     * getLoginInputFilter
     *
     * @return inputFilter
     */
    private function getStoreValidator($login, $password)
    {
        return ($login && $password);
    }

    /**
     * dispatchError
     *
     * @param int $httpCode
     * @param string $errorMessage
     */
    private function dispatchError($httpCode, $errorMessage)
    {
        $resp = $this->getApp()->getResponse();
        $resp->setType($resp::TYPE_JSON);
        $resp->setHttpCode($httpCode);
        $resp->setContent([
            self::AUTH_ERROR => true,
            self::AUTH_ERROR_MESSAGE => $errorMessage
        ]);
        $resp->dispatch($andDie = true);
    }
}
