<?php

/**
 * Description of App1\Form\Users\Lostpassword
 *
 * @author pierrefromager
 */

namespace App1\Form\Users;

use Pimvc\Form;

class Lostpassword extends Form
{

    const PWD_ACTION = '/user/lostpassword';
    const EMAIL_VALIDATOR = 'isemail';
    const PWD_METHOD = 'post';
    const PWD_FORM_NAME = 'user-lostpwd';
    const PWD_FORM_WIDTH = 300;
    const PWD_PARAM_EMAIL = 'email';
    const PWD_TYPE_EMAIL = self::PWD_PARAM_EMAIL;

    protected $baseUrl = '';

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return \App1\Form\Users\Lostpassword
     */
    public function __construct($postedDatas)
    {
        $this->baseUrl = \Pimvc\App::getInstance()->getRequest()->getBaseUrl();
        parent::__construct(
            $this->_getFields(),
            self::PWD_FORM_NAME,
            $this->baseUrl . self::PWD_ACTION,
            self::PWD_METHOD,
            $postedDatas,
            array()
        );
        $this->setAlign('normal');
        $this->setLabels($this->_getLabels());
        $this->setValidators($this->_getValidators());
        $this->setOptions(['width' => self::PWD_FORM_WIDTH]);
        $this->setFormWrapperId('wrapper-form-lost-password');
        $this->setWrapperClass('email', 'form-element-wrapper col-sm-12');
        $this->setClass('email', 'form-control');
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
     * _getLabels
     *
     * @return array
     */
    private function _getLabels()
    {
        return [self::PWD_PARAM_EMAIL => 'Adresse Email'];
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return [self::PWD_PARAM_EMAIL];
    }

    /**
     * _getValidators
     *
     * @return array
     */
    private function _getValidators()
    {
        $fields = $this->_getFields();
        $validatorsName = array_fill(0, count($fields), self::EMAIL_VALIDATOR);
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
        $typesName = array_fill(0, count($fields), self::PWD_TYPE_EMAIL);
        $types = array_combine($fields, $typesName);
        return $types;
    }
}
