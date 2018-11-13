<?php
/**
 * Description of App1\Form\Users\Login
 *
 * @author pierrefromager
 */
namespace App1\Form\Users;

use Pimvc\Form;
use App1\Helper\Lang\IEntries as ILang;

class Login extends Form implements ILang
{
    const LOGIN_ACTION = '/user/login';
    const LOGIN_METHOD = 'post';
    const LOGIN_FORM_NAME = 'user-login';
    const LOGIN_FORM_WIDTH = '300px';

    protected $baseUrl = '';
    protected $app;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return \Form_Users_Login
     */
    public function __construct($postedDatas)
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $fields = array('login', 'password', self::FORM_XCSRF);
        $formAction = $this->baseUrl . self::LOGIN_ACTION;
        parent::__construct(
            $fields,
            self::LOGIN_FORM_NAME,
            $formAction,
            self::LOGIN_METHOD,
            $postedDatas,
            []
        );
        $labelList = [
        $this->translate(ILang::__EMAIL),
            $this->translate(ILang::__PASSWORD),
            'XCsrf'
        ];
        $labels = array_combine($fields, $labelList);
        $this->setLabels($labels);
        $postedXcsrf = (isset($postedDatas[self::FORM_XCSRF])) ? $postedDatas[self::FORM_XCSRF]
            : \App1\Form\Csrf::generate(
                self::FORM_XCSRF,
                $withOriginCheck = true
            );
        $this->setValue(self::FORM_XCSRF, $postedXcsrf);
        $validators = [
            'login' => 'isemail'
            , 'password' => 'ispassword-5_12'
            , self::FORM_XCSRF => 'validxcsrf'
        ];
        $this->setValidators($validators);
        $this->setAlign('normal');
        $this->setAction($formAction);
        $this->setWrappers();
        $this->setValidLabelButton('Connexion');
        $this->setFormClass('form row');
        $this->render();
        return $this;
    }

    /**
     * setWrappers
     *
     */
    private function setWrappers()
    {
        $this->setFormWrapperId('wrapper-form-login');
        $this->setWrapperClass('login', 'form-element-wrapper col-sm-12');
        $this->setClass('login', 'form-control');
        $this->setWrapperClass('password', 'form-element-wrapper col-sm-12');
        $this->setClass('password', 'form-control');
        $this->setWrapperClass('xcsrf', 'hidden');
    }

    /**
     * translate
     *
     * @param string $key
     * @return string
     */
    private function translate(string $key): string
    {
        return $this->app->getTranslator()->translate($key);
    }
}
