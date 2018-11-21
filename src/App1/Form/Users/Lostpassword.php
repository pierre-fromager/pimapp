<?php

/**
 * Description of App1\Form\Users\Lostpassword
 *
 * @author pierrefromager
 */

namespace App1\Form\Users;

use Pimvc\Form;
use App1\Helper\Lang\IEntries as ILang;

class Lostpassword extends Form
{

    const PWD_ACTION = '/user/lostpassword';
    const PWD_METHOD = 'post';
    const PWD_FORM_NAME = 'user-lostpwd';
    const PWD_FORM_WIDTH = 300;
    const _EMAIL = 'email';
    const _WIDTH = 'width';

    private $app;
    protected $baseUrl = '';

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return \App1\Form\Users\Lostpassword
     */
    public function __construct($postedDatas)
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        parent::__construct(
            $this->_getFields(),
            self::PWD_FORM_NAME,
            $this->baseUrl . self::PWD_ACTION,
            self::PWD_METHOD,
            $postedDatas,
            []
        );
        $this->setAlign('normal');
        $this->setLabels($this->_getLabels());
        $this->setValidators($this->_getValidators());
        $this->setOptions([self::_WIDTH => self::PWD_FORM_WIDTH]);
        $this->setWrappers();
        $this->setTypes($this->_getTypes());
        $this->setsectionsize(3);
        $this->render();
        return $this;
    }

    /**
     * isValid
     *
     * @return boolean
     */
    public function isValid()
    {
        return parent::isValid();
    }

    /**
     * setWrappers
     *
     */
    private function setWrappers()
    {
        $this->setFormWrapperId('wrapper-form-lost-password');
        $this->setWrapperClass('email', 'form-element-wrapper col-sm-12');
        $this->setClass('email', 'form-control');
    }

    /**
     * _getLabels
     *
     * @return array
     */
    private function _getLabels()
    {
        return [self::_EMAIL => $this->translate(ILang::__EMAIL)];
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return [self::_EMAIL];
    }

    /**
     * _getValidators
     *
     * @return array
     */
    private function _getValidators()
    {
        $fields = $this->_getFields();
        $validatorsName = array_fill(0, count($fields), 'isemail');
        $validators = array_combine($fields, $validatorsName);
        return $validators;
    }

    /**
     * _getTypes
     *
     * @return array
     */
    private function _getTypes()
    {
        $fields = $this->_getFields();
        $typesName = array_fill(0, count($fields), self::_EMAIL);
        $types = array_combine($fields, $typesName);
        return $types;
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
