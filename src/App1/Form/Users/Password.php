<?php

/**
 * App1\Form\Users\Password
 *
 * @author pierrefromager
 */
namespace App1\Form\Users;

use Pimvc\Form;
use Pimvc\Tools\Session as sessionTool;

class Password extends Form
{

    const PWD_ACTION = '/user/changepassword';
    const PWD_VALIDATOR = 'ispassword-5_12';
    const PWD_METHOD = 'post';
    const PWD_FORM_NAME = 'user-pwdchange';
    const PWD_FORM_WIDTH = 300;
    const PWD_PARAM_ID = 'id';
    const PWD_TYPE_PASSWORD = 'password';
    
    protected $baseUrl = '';
    private $uid = '';

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return \Form_Users_Changepassword
     */
    public function __construct($postedDatas)
    {
        $this->uid = (isset($postedDatas['id']))
            ? $postedDatas['id']
            : sessionTool::getUid();
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $formAction = $this->baseUrl . self::PWD_ACTION;
        parent::__construct(
            $this->_getFields(),
            self::PWD_FORM_NAME,
            $formAction,
            self::PWD_METHOD,
            $postedDatas,
            array()
        );
        $this->setAlign('left');
        $this->setLabels($this->_getLabels());
        $this->setValidators($this->_getValidators());
        //$formOptions = array('width' => self::PWD_FORM_WIDTH);
        //$this->setOptions($formOptions);
        $this->setAction($formAction);
        $this->setTypes($this->_getTypes());
        $this->setType(self::PWD_PARAM_ID, 'hidden');
        $this->setValue(self::PWD_PARAM_ID, $this->uid);
        $this->Setsectionsize(5);
        $this->setWrapperClass('oldpassword', 'form-element-wrapper col-sm-12');
        $this->setClass('oldpassword', 'form-control');
        $this->setWrapperClass('newpassword1', 'form-element-wrapper col-sm-12');
        $this->setClass('newpassword1', 'form-control');
        $this->setWrapperClass('newpassword2', 'form-element-wrapper col-sm-12');
        $this->setClass('newpassword2', 'form-control');
        $this->render();
        return $this;
    }
    
    /**
     * _getLabels
     *
     * @return array
     */
    private function _getLabels()
    {
        return array(
            self::PWD_PARAM_ID => ''
            , 'oldpassword' => 'Ancien mot de passe'
            , 'newpassword1' => 'Nouveau mot de passe'
            , 'newpassword2' => 'Re-saisir nouveau mot de passe'
        );
    }
    
    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return array_keys($this->_getLabels());
    }
    
    /**
     * _getValidators
     *
     * @return array
     */
    private function _getValidators()
    {
        $fields = $this->_getFields();
        $validatorsName = array_fill(0, count($fields), self::PWD_VALIDATOR);
        $validators = array_combine($fields, $validatorsName);
        unset($validators[self::PWD_PARAM_ID]);
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
        $typesName = array_fill(0, count($fields), self::PWD_TYPE_PASSWORD);
        $types = array_combine($fields, $typesName);
        unset($types[self::PWD_PARAM_ID]);
        return $types;
    }

    /**
     * isValid
     *
     * @return bool
     */
    public function isValid()
    {
        return parent::isValid();
    }
}
