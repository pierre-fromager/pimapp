<?php
/**
 *
 * Description of App1\Controller\Error
 *
 * @author Pierre Fromager
 */
namespace App1\Controller;

use \Pimvc\Controller\Basic as basicController;

final class Error extends basicController
{
    const VIEW_PATH = '/Views/Error/';
    const VIEW_FILE = 'Index.php';
    const LAYOUT_NAME = 'responsive';
    const HTTP_CODE_ERRORS = [1 => 404, 6 => 400, 7 => 403, 3 => 500];
    const PARAM_CONTEXT = 'context';
    const PARAM_JSON = 'json';
    const PARAM_ERRORS = 'errors';
    const PARAM_CONTROLLER = 'controller';
    const PARAM_ACTION = 'action';
    const PARAM_ROUTER = 'router';
    const PARAM_REQUEST_PARAMS = 'request_params';
    const PARAM_REQUEST = 'request';
    const PARAM_SERVER = 'server';
    const PARAM_PARAMS = 'params';
    const PARAM_CODE = 'code';
    const PHP_EXT = '.php';
    const EMPTYS = '';

    protected $baseUrl;
    protected $request;
    protected $currentAction;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->request = $this->getApp()->getRequest();
        $this->baseUrl = $this->request->getBaseUrl();
    }

    /**
     * index
     *
     * @return \Pimvc\Http\Response
     */
    final public function index()
    {
        $this->currentAction = __FUNCTION__;
        $errors = $this->getErrorParams();
        $errorCode = $errors[self::PARAM_ERRORS][0][self::PARAM_CODE];
        return ($this->isJsonContext()) ? $this->getJsonResponse($errors) : $this->getHtmlResponse(
            $this->getLayout(
                $this->getView($errors, $this->getErrorViewName())
            ),
            self::EMPTYS,
            self::EMPTYS,
            self::HTTP_CODE_ERRORS[$errorCode]
        );
    }

    /**
     * getErrorViewName
     *
     * @return string
     */
    private function getErrorViewName()
    {
        return self::VIEW_PATH . ucfirst($this->currentAction) . self::PHP_EXT;
    }

    /**
     * getContext
     *
     * @return string
     */
    private function getContext()
    {
        $tupples = $this->request->getQueryTupple();
        return (isset($tupples[self::PARAM_CONTEXT])) ? $tupples[self::PARAM_CONTEXT] : '';
    }

    /**
     * isJsonContext
     *
     * @return boolean
     */
    private function isJsonContext()
    {
        $isJsonContentType = ($this->request->contentType() === \Pimvc\Http\Interfaces\Request::HEADER_CONTENT_TYPE_JSON);
        return ($this->getContext() === self::PARAM_JSON || $isJsonContentType);
    }

    /**
     * getErrorParams
     *
     * @return array
     */
    private function getErrorParams()
    {
        $errorParams = [
            self::PARAM_ERRORS => $this->getParams(self::PARAM_ERRORS),
            self::PARAM_CONTROLLER => $this->getParams(self::PARAM_CONTROLLER),
            self::PARAM_ACTION => $this->getParams(self::PARAM_ACTION),
            self::PARAM_ROUTER => $this->getParams(self::PARAM_ROUTER),
            self::PARAM_REQUEST => $this->getParams(self::PARAM_REQUEST),
            self::PARAM_REQUEST_PARAMS => $this->getParams(self::PARAM_REQUEST_PARAMS),
        ];
        if (!$this->isJsonContext()) {
            $errorParams['nav'] = (string) $this->getNav();
        } else {
            $errorParams[self::PARAM_SERVER] = $this->request->getServer();
            $errorParams[self::PARAM_PARAMS] = $this->request->getParams();
        }
        return $errorParams;
    }

    /**
     * getLayout
     *
     * @param string $content
     * @return \App1\Views\Helpers\Layouts\Responsive
     */
    private function getLayout($content)
    {
        $layout = (new \App1\Views\Helpers\Layouts\Responsive());
        $layoutParams = ['content' => $content];
        $layout->setApp($this->getApp())
            ->setName(self::LAYOUT_NAME)
            ->setLayoutParams($layoutParams)
            ->build();
        return $layout;
    }

    /**
     * getNav
     *
     * @return \App1\Views\Helper\Bootstrap\Nav
     */
    private function getNav()
    {
        $nav = (new \App1\Views\Helpers\Bootstrap\Nav());
        $nav->setParams($this->getNavConfig())->render();
        return $nav;
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    private function getNavConfig()
    {
        return [
            'title' => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-home',
                'link' => $this->baseUrl
            ],
            'items' => []
        ];
    }
}
