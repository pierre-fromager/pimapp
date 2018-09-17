<?php
/**
 * App1\Form\Metro\Lignes\Search
 *
 * @author pierrefromager
 */
namespace App1\Form\Metro\Lignes;

use Pimvc\Form;
use App1\Form\Metro\Lignes\Edit as editMetroLignesForm;
use App1\Model\Metro\Lignes as modelLignes;
use App1\Model\Metro\Stations as modelStations;

class Search extends Form
{
    const METRO_SEARCH_ACTION = '/metro/lignes/manage';
    const METRO_VOLUME_ACTION = '/metro/lignes/volumes';
    const METRO_SEARCH_FORM_NAME = 'metro-lignes-search';
    const METRO_SEARCH_FORM_METHOD = 'POST';
    const METRO_FORM_DECORATOR_BREAK = '<br style="clear:both">';
    const TYPE_SELECT = 'select';

    private $lignesModel;
    private $stationsModel;

    /**
     * @see __construct
     *
     * @param array $postedDatas
     * @return \Form_Users_Search
     */
    public function __construct($postedDatas)
    {
        $formAction = \Pimvc\App::getInstance()->getRequest()->getBaseUrl()
            . self::METRO_SEARCH_ACTION;
        parent::__construct(
            $this->_getFields(),
            self::METRO_SEARCH_FORM_NAME,
            $formAction,
            self::METRO_SEARCH_FORM_METHOD,
            $postedDatas
        );
        $searchLabels = editMetroLignesForm::_getStaticLabels();
        $this->setLabels($searchLabels);
        $this->setAction($formAction);
        $this->_setModel();
        $this->_setListes($postedDatas);
        $this->_setWrappers();
        $this->setSearchMode('true');
        $this->setValues($postedDatas);
        $this->render();
        return $this;
    }

    /**
     * changeAction
     *
     * @param string $action
     * @return $this
     */
    public function changeAction($action = '')
    {
        if ($action) {
            $this->setAction($action);
        }
        return $this;
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return [
            modelLignes::_LIGNE, modelLignes::_HSRC, modelLignes::_HDST
        ];
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
        $cols4 = $elementWrapper . ' col-sm-4';
        $formControl = 'form form-control';
        $this->setWrapperClass(modelLignes::_LIGNE, $cols4);
        $this->setClass(modelLignes::_LIGNE, $formControl);
        $this->setWrapperClass(modelLignes::_HSRC, $cols4);
        $this->setClass(modelLignes::_HSRC, $formControl);
        $this->setWrapperClass(modelLignes::_HDST, $cols4);
        $this->setClass(modelLignes::_HDST, $formControl);
    }

    /**
     * _setListes
     *
     * @param array $postedDatas
     */
    private function _setListes($postedDatas)
    {
        $ligneNames = array_map(function ($v) {
            return $v['ligne'];
        }, $this->lignesModel->getLignesValues());

        $this->setType(modelLignes::_LIGNE, self::TYPE_SELECT);
        $this->setData(modelLignes::_LIGNE, array_combine($ligneNames, $ligneNames));

        $stationsH = $this->stationsModel->getByH();
        $stationsHNames = array_map(
            function ($v) {
                return $v['name'];
            },
            $stationsH
        );

        $this->setType(modelLignes::_HSRC, self::TYPE_SELECT);
        $this->setData(modelLignes::_HSRC, $stationsHNames);

        $this->setType(modelLignes::_HDST, self::TYPE_SELECT);
        $this->setData(modelLignes::_HDST, $stationsHNames);
    }

    /**
     * _setModel
     *
     */
    private function _setModel()
    {
        $dbConf = \Pimvc\App::getInstance()->getConfig()->getSettings('dbPool');
        $this->lignesModel = new modelLignes($dbConf);
        $this->stationsModel = new modelStations($dbConf);
    }
}
