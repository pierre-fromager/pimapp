<?php

/**
 * App1\Form\Crud\Search
 *
 * @author pierrefromager
 */

namespace App1\Form\Crud;

use Pimvc\Form;

class Search extends Form
{

    const CRUD_SEARCH_ACTION = 'crud/manage';
    const CRUD_SEARCH_FORM_NAME = 'crud-search';
    const CRUD_SEARCH_FORM_METHOD = 'POST';
    const CRUD_SEARCH_DECORATOR_BREAK = '<br style="clear:both"/>';

    protected $isAdmin;
    protected $postedData;
    protected $app;
    protected $baseUrl;
    private $tableFields;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @param array $tableFields
     * @return $this
     */
    public function __construct($postedDatas, $tableFields)
    {
        $this->tableFields = $tableFields;
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->postedData = $postedDatas;

        parent::__construct(
            $tableFields,
            self::CRUD_SEARCH_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::CRUD_SEARCH_ACTION,
            self::CRUD_SEARCH_FORM_METHOD,
            $this->postedData
        );
        $this->_setWrappers();
        $this->setSearchMode('true');
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Rechercher');
        $this->render();
        return $this;
    }

    /**
     * _setWrappers
     *
     */
    private function _setWrappers()
    {
        $elementWrapper = 'form-element-wrapper';
        $cols6 = $elementWrapper . ' col-sm-6';
        $formControl = 'form form-control';
        foreach ($this->tableFields as $field) {
            if ($field != 'id') {
                $this->setWrapperClass($field, $cols6);
                $this->setClass($field, $formControl);
            }
        }
    }
}
