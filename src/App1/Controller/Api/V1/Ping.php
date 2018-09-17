<?php
/**
 * Description of App1\Controller\Api\V1\Ping
 *
 * @author Pierre Fromager
 */
namespace App1\Controller\Api\V1;

use Pimvc\Input\Filter as inputFilter;
use Pimvc\Controller\Response as RestfulController;
use Pimvc\Controller\Interfaces\Restful as RestfulInterface;

class Ping extends RestfulController implements RestfulInterface
{

    private $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->baseUrl = $this->getApp()
            ->getInstance()
            ->getRequest()
            ->getBaseUrl();
    }

    /**
     * index
     *
     */
    final public function index()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    final public function create()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    final public function destroy()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    final public function edit()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    final public function show()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    final public function store()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    final public function update()
    {
        return $this->response(__FUNCTION__, 'message');
    }

    private function response($action, $data)
    {
        $payload = [
            'action' => $action,
            'router_uri' => $this->getApp()->getRouter()->getUri(),
            'request_uri' => $this->getApp()->getRequest()->getUri(),
            'method' => $this->getApp()->getRequest()->getMethod(),
            'params' => $this->getParams(),
            'data' => $data
        ];
        return $this->getJsonResponse($payload);
    }

    /**
     * getLoginInputFilter
     *
     * @return inputFilter
     */
    private function getFilter($postedDatas)
    {
        return new inputFilter(
            $postedDatas,
            [
            /* self::PARAM_ROWS => FILTER_SANITIZE_NUMBER_INT,
              self::PARAM_COLS => FILTER_SANITIZE_NUMBER_INT,
              self::PARAM_MIN => FILTER_SANITIZE_NUMBER_INT,
              self::PARAM_MAX => FILTER_SANITIZE_NUMBER_INT,
              self::PARAM_CALLBACK => FILTER_SANITIZE_STRING, */
            ]
        );
    }
}
