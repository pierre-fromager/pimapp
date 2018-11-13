<?php
/**
 * App1\Form\Users\Search
 *
 * @author pierrefromager
 */
namespace App1\Form\Users;

use Pimvc\Form;
use App1\Form\Users\Edit as editUsersForm;
use App1\Helper\Lang\IEntries as ILang;
use App1\Model\Users as modelUser;

class Search extends Form
{
    const USER_SEARCH_ACTION = '/user/manage';
    const USER_SEARCH_FORM_NAME = 'user-search';
    const USER_SEARCH_FORM_METHOD = 'POST';
    const USER_FORM_DECORATOR_BREAK = '<br style="clear:both">';

    protected $app;
    protected $baseUrl;

    /**
     * @see __construct
     *
     * @param array $postedDatas
     * @return \Form_Users_Search
     */
    public function __construct($postedDatas)
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        parent::__construct(
            $this->_getFields(),
            self::USER_SEARCH_FORM_NAME,
            $this->baseUrl . self::USER_SEARCH_ACTION,
            self::USER_SEARCH_FORM_METHOD,
            $postedDatas
        );
        $this->setLabels(editUsersForm::_getStaticLabels());
        $this->setType('profil', 'select');
        $roles = array_flip(\App1\Helper\Format\Roles::getList());
        $this->setData('profil', $roles);
        $this->setType('status', 'select');
        $this->setData('status', array(
            'waiting' => 'En attente'
            , 'valid' => 'Validé'
        ));
        $this->setExtra('gsm', self::USER_FORM_DECORATOR_BREAK);
        $this->_setWrappers();
        $this->setSearchMode('true');
        $this->setSearchWrapperTitle($this->translate(ILang::__CRITERIAS));
        $this->render();
        return $this;
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return array(
            'name'
            , 'email'
            , 'login'
            , 'profil'
            , 'status'
            , 'fid'
            , 'adresse'
            , 'cp'
            , 'ville'
            , 'gsm'
            , 'age'
            , 'sexe'
            , 'reference'
        );
    }

    /**
     * _getLabels
     *
     * @return array
     */
    private function _getLabels()
    {
        return array_combine($this->_fields, $this->_labels);
    }

    /**
     * _setWrappers
     *
     */
    private function _setWrappers()
    {
        $elementWrapper = 'form-element-wrapper';
        $cols2 = $elementWrapper . ' col-sm-2';
        $cols4 = $elementWrapper . ' col-sm-4';
        $cols6 = $elementWrapper . ' col-sm-6';
        $cols12 = $elementWrapper . ' col-sm-12';
        $formControl = 'form form-control';

        $this->setWrapperClass('name', $cols4);
        $this->setClass('name', $formControl);
        $this->setWrapperClass('email', $cols4);
        $this->setClass('email', $formControl);
        $this->setWrapperClass('login', $cols4);
        $this->setClass('login', $formControl);

        $this->setWrapperClass('profil', $cols6);
        $this->setClass('profil', $formControl);
        $this->setWrapperClass('status', $cols6);
        $this->setClass('status', $formControl);

        $this->setWrapperClass('fid', $cols12);
        $this->setClass('fid', $formControl);

        $this->setWrapperClass('adresse', $cols6);
        $this->setClass('adresse', $formControl);
        $this->setWrapperClass('cp', $cols2);
        $this->setClass('cp', $formControl);
        $this->setWrapperClass('ville', $cols4);
        $this->setClass('ville', $formControl);

        $this->setWrapperClass('gsm', $cols6);
        $this->setClass('gsm', $formControl);
        $this->setWrapperClass('age', $cols2);
        $this->setClass('age', $formControl);
        $this->setWrapperClass('sexe', $cols2);
        $this->setClass('sexe', $formControl);
        $this->setWrapperClass('reference', $cols2);
        $this->setClass('reference', $formControl);
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
