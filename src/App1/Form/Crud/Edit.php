<?php

/**
 * App1\Form\Crud\Edit
 *
 * @author pierrefromager
 */

namespace App1\Form\Crud;

use Pimvc\Form;

class Edit extends Form
{

    const CRUD_EDIT_ACTION = 'crud/edit';
    const CRUD_EDIT_FORM_NAME = 'crud-edit';
    const CRUD_EDIT_FORM_METHOD = 'POST';
    const CRUD_EDIT_DECORATOR_BREAK = '<br style="clear:both"/>';

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
            self::CRUD_EDIT_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::CRUD_EDIT_ACTION,
            self::CRUD_EDIT_FORM_METHOD,
            $this->postedData
        );
        $this->_setWrappers();
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Valider');
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
