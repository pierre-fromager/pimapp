<?php
/**
 * Description of App1\Form\Users\Login
 *
 * @author pierrefromager
 */
namespace App1\Form\Users;

use Pimvc\Form;

class Login extends Form
{
    const LOGIN_ACTION = '/user/login';
    const LOGIN_METHOD = 'post';
    const LOGIN_FORM_NAME = 'user-login';
    const LOGIN_FORM_WIDTH = '300px';

    protected $baseUrl = '';

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return \Form_Users_Login
     */
    public function __construct($postedDatas)
    {
        $this->baseUrl = \Pimvc\App::getInstance()->getRequest()->getBaseUrl();
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
        $labelList = array(
            'Adresse email', //Helper_Glyph::get(Helper_Glyph::envelope) .
            'Mot de passe', //, Helper_Glyph::get(Helper_Glyph::lock) .
            'XCsrf'
        );
        $labels = array_combine($fields, $labelList);
        $this->setLabels($labels);

//        $formOptions = array('style' => 'max-width:' . self::LOGIN_FORM_WIDTH);
//        $this->setOptions($formOptions);
        $postedXcsrf = (isset($postedDatas[self::FORM_XCSRF]))
            ? $postedDatas[self::FORM_XCSRF]
            : \App1\Form\Csrf::generate(
                self::FORM_XCSRF,
                $withOriginCheck = true
            );
        $this->setValue(
            self::FORM_XCSRF,
            $postedXcsrf
        );
        $validators = array(
            'login' => 'isemail'
            , 'password' => 'ispassword-5_12'
            , self::FORM_XCSRF => 'validxcsrf'
        );
        $this->setValidators($validators);
        $this->setAlign('normal');
        $this->setFormWrapperId('wrapper-form-login');
        $this->setAction($formAction);

        $this->setWrapperClass('login', 'form-element-wrapper col-sm-12');
        $this->setClass('login', 'form-control');

        $this->setWrapperClass('password', 'form-element-wrapper col-sm-12');
        $this->setClass('password', 'form-control');

        $this->setWrapperClass('xcsrf', 'hidden');

        $this->setValidLabelButton("Connexion");
        $this->setFormClass('form row');
        $this->render();
        return $this;
    }
}
