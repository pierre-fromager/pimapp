<?php
/**
 * App1\Form\Metro\Lignes\Itineraire
 *
 * @author pierrefromager
 */
namespace App1\Form\Metro\Lignes;

use Pimvc\Form;
use App1\Form\Metro\Lignes\Edit as editMetroLignesForm;
use App1\Model\Metro\Lignes as modelLignes;
use App1\Model\Metro\Stations as modelStations;

class Itineraire extends Form
{

    const METRO_SEARCH_ITI_ACTION = '/metro/lignes/search';
    const METRO_SEARCH_FORM_NAME = 'metro-iti-search';
    const METRO_SEARCH_FORM_METHOD = 'POST';
    const METRO_FORM_DECORATOR_BREAK = '<br style="clear:both">';
    const TYPE_SELECT = 'select';
    const _OPTIM = 'optim';
    const _OPTIM_LABEL = 'Optimisation';
    const _WEIGHTED = 'weighted';
    const _UNWEIGHTED = 'unweighted';
    const _OPTIM_TYPES = [self::_UNWEIGHTED => 'Correspondances', self::_WEIGHTED => 'Distances'];

    private $stationsModel;

    /**
     * @see __construct
     *
     * @param array $postedDatas
     * @return \App1\Form\Metro\Lignes\Itineraire
     */
    public function __construct($postedDatas)
    {
        parent::__construct(
            $this->_getFields(),
            self::METRO_SEARCH_FORM_NAME,
            self::METRO_SEARCH_ITI_ACTION,
            self::METRO_SEARCH_FORM_METHOD,
            $postedDatas
        );
        $searchLabels = editMetroLignesForm::_getStaticLabels();
        $searchLabels += [self::_OPTIM => self::_OPTIM_LABEL];
        if (!isset($postedDatas[self::_OPTIM])) {
            $postedDatas[self::_OPTIM] = self::_UNWEIGHTED;
        }
        $this->setLabels($searchLabels);
        $this->setLabel('hsrc', 'Départ');
        $this->setLabel('hdst', 'Arrivée');
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
            self::_OPTIM, modelLignes::_HSRC, modelLignes::_HDST
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
        $this->setWrapperClass(self::_OPTIM, $cols4);
        $this->setClass(self::_OPTIM, $formControl);
        $this->setWrapperClass(modelLignes::_HSRC, $cols4);
        $this->setClass(modelLignes::_HSRC, $formControl);
        $this->setWrapperClass(modelLignes::_HDST, $cols4);
        $this->setClass(modelLignes::_HDST, $formControl);
    }

    /**
     * _setListes
     *
     */
    private function _setListes()
    {
        $stationsH = $this->stationsModel->getByH();
        $stationsHNames = array_map(
            function ($v) {
                return $v[modelStations::_NAME];
            },
            $stationsH
        );

        $this->setType(self::_OPTIM, self::TYPE_SELECT);
        $this->setData(self::_OPTIM, self::_OPTIM_TYPES);

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
        $this->stationsModel = new modelStations($dbConf);
    }
}
