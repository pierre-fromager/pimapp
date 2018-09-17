<?php
/**
 * App1\Form\Metro\Lignes\Search
 *
 * @author pierrefromager
 */
namespace App1\Form\Metro\Stations;

use Pimvc\Form;
use App1\Form\Metro\Stations\Edit as editMetroStationsForm;
use App1\Model\Metro\Lignes as modelLignes;
use App1\Model\Metro\Stations as modelStations;

class Search extends Form
{

    const METRO_SEARCH_ACTION = '/metro/stations/manage';
    const METRO_VOLUME_ACTION = '/metro/stations/volumes';
    const METRO_SEARCH_FORM_NAME = 'metro-stations-search';
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
        $searchLabels = editMetroStationsForm::_getStaticLabels();
        $this->setLabels($searchLabels);
        $this->setAction($formAction);
        $this->_setModel();
        $this->_setListes();
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
        return [modelStations::_H];
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
        $cols12 = $elementWrapper . ' col-sm-12';
        $formControl = 'form form-control';
        $this->setWrapperClass(modelStations::_H, $cols12);
        $this->setClass(modelStations::_H, $formControl);
    }

    /**
     * _setListes
     *
     * @param array $postedDatas
     */
    private function _setListes()
    {
        $stationsHNames = array_map(
            function ($v) {
                return $v[modelStations::_NAME];
            },
            $this->stationsModel->getByH()
        );
        $this->setType(modelStations::_H, self::TYPE_SELECT);
        $this->setData(modelStations::_H, $stationsHNames);
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
