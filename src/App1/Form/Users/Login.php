<?php

/**
 * Description of App1\Form\Users\Login
 *
 * @author pierrefromager
 */

namespace App1\Form\Users;

use Pimvc\Form;
use \App1\Helper\Lang\IEntries as ILang;
use \App1\Model\Users as modelUser;
use \App1\Form\Csrf as csrfGenerator;

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
        $fields = $this->_getFields();
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
        $this->_setCsrf($postedDatas);
        $this->_setValidators();
        $this->setAlign('normal');
        $this->_setWrappers();
        $this->setValidLabelButton(
            $this->translate(ILang::__USERS_SIGN_IN)
        );
        $this->setFormClass('form row');
        $this->render();
        return $this;
    }

    /**
     * translate
     *
     * @param string $key
     * @return string
     */
    protected function translate(string $key): string
    {
        return $this->app->getTranslator()->translate($key);
    }

    /**
     * _setCsrf
     *
     * @param array $postedDatas
     */
    private function _setCsrf(array $postedDatas)
    {
        
        $hasXcsrf = isset($postedDatas[self::FORM_XCSRF]);
        $postedXcsrf = ($hasXcsrf) ? $postedDatas[self::FORM_XCSRF] : csrfGenerator::generate(self::FORM_XCSRF, true);
        $this->setValue(self::FORM_XCSRF, $postedXcsrf);
    }

    /**
     * _setValidators
     *
     */
    private function _setValidators()
    {
        $validators = [
            modelUser::_LOGIN => 'isemail'
            , modelUser::_PASSWORD => 'ispassword-5_30'
            , self::FORM_XCSRF => 'validxcsrf'
        ];
        $this->setValidators($validators);
    }

    /**
     * setWrappers
     *
     */
    private function _setWrappers()
    {
        $wrappedFields = [modelUser::_LOGIN, modelUser::_PASSWORD];
        foreach ($wrappedFields as $wrappedField) {
            $this->setWrapperClass($wrappedField, 'form-element-wrapper col-sm-12');
            $this->setClass($wrappedField, 'form-control');
        }
        $this->setWrapperClass('xcsrf', 'hidden');
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields(): array
    {
        return [
            modelUser::_LOGIN,
            modelUser::_PASSWORD,
            self::FORM_XCSRF
        ];
    }
}
