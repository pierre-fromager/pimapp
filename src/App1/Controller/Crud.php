<?php

/**
 * Description of App1\Controller\Crud
 *
 * @author Pierre Fromager
 */

namespace App1\Controller;

use \Pimvc\Tools\Flash as flashTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Views\Helpers\Fa as faHelper;
use \Pimvc\Db\Model\Field as modelField;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use App1\Form\Crud\Select as tableSelectForm;
use App1\Form\Crud\Search as crudSearchForm;
use App1\Form\Crud\Edit as crudEditForm;
use App1\Helper\Controller\Crud as helperCrudController;

final class Crud extends helperCrudController
{

    //use \App1\Helper\Reuse\Controller;
    use \Pimvc\Db\Charset\Convert;

    /**
     * index
     *
     * @return string
     */
    final public function index()
    {
        $ready = ($this->slot && $this->table);
        $criterias = [self::_SLOT => $this->slot, self::_TABLE => $this->table];
        $form = new tableSelectForm($criterias);
        $linkManage = ($ready) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . DIRECTORY_SEPARATOR . 'crud/manage',
            [self::_TITLE => 'Crud manager']
        ) : '';
        $table = '';
        if ($this->fields) {
            $tableDatas = $this->fields->toArray();
            $tableHeaders = array_keys($tableDatas[0]);
            $table = new \Pimvc\Views\Helpers\Table('', $tableHeaders, $tableDatas);
            $table->setId('crud-columns');
            $table->setClass('table-6 managetable');
            $table->render();
        }
        $title = 'Database table selection';
        $widgetTile = ($ready) ? $title . $this->getWidgetLinkWrapper($linkManage) : $title;
        $widget = $this->getWidget(
            faHelper::get(faHelper::DATABASE) . $widgetTile,
            (string) $form . $this->getListeTableResponsive((string) $table)
        );
        unset($form, $table);
        flashTools::add(
            ($ready) ? flashTools::FLASH_SUCCESS : flashTools::FLASH_WARNING,
            ($ready) ? 'Ready to CRUD' : 'Missing param'
        );
        $view = $this->getView(['content' => (string) $widget], self::VIEW_INDEX);
        unset($widget);
        return (string) $this->getLayout((string) $view);
    }

    /**
     * manage
     *
     * return Response
     */
    final public function manage()
    {
        $ready = ($this->slot && $this->table && $this->tableExists === true);
        if (!$ready) {
            return $this->redirect($this->baseUrl . '/crud/index');
        }
        $this->setPageSize();
        $is4d = (\Pimvc\Db\Model\Core::MODEL_ADAPTER_4D === $this->adapter);
        if ($is4d) {
            $indexedFieldList = $this->fields->getIndexes(true);
            $fieldList = $searchFields = $indexedFieldList;
        } else {
            $fieldList = array_map(function ($v) {
                return $v['name'];
            }, $this->fields->toArray());
            $searchFields = $fieldList;
        }
        $criterias = $this->getSearchAssist();
        $form = new crudSearchForm($criterias, $this->fields, $this->operators);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get((string) $form);
        unset($form);
        $liste = $this->getListe($this->crudInstance, $criterias, $fieldList);
        if ($context = $this->getParams('context')) {
            if ($context === 'json') {
                return $this->getJsonResponse($liste->getJson());
            } elseif ($context === 'xml') {
                $this->getXmlHeaders();
                echo \Pimvc\Tools\Xml\Serializer::generateValidXmlFromObj(
                    $liste->getJson(),
                    'crud',
                    'part'
                );
                die;
            }
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::SEARCH)
                . 'Gestion Crud ( ' . $this->table . ' )'
                . $this->getManageLinks(),
            $filter . $this->getListeTableResponsive($liste)
        );
        unset($filter);
        unset($liste);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * edit
     *
     * @return string
     */
    final public function edit()
    {
        $id = $this->getParams(self::_ID);
        $mode = $this->getParams('mode');
        $isModeDetail = ($mode === 'detail');
        $fieldList = array_map(function (modelField $v) {
            return $v->getName();
        }, iterator_to_array($this->fields));
        $timeFields = $booleansFields = $datesFields = $realFields = [];
        if ($this->is4d()) {
            $timeFields = $this->crudInstance->getDomainInstance()->getFourdTimeFields();
            $booleansFields = $this->crudInstance->getDomainInstance()->getBooleansFields();
            $datesFields = $this->crudInstance->getDomainInstance()->getFourdDatesFields();
            $realFields = $this->crudInstance->getDomainInstance()->getFourdRealFields();
        }
        $datas = [];
        if ($this->isPost() && !$isModeDetail) {
            $pdoTypes = $this->crudInstance->getDomainInstance()->getPdos();
            $params = $datas = $this->getParams();
            if ($this->is4d()) {
                $this->prepareSaveFourdDatas($params, $pdoTypes, $timeFields, $booleansFields, $datesFields, $realFields);
            }
            // Run prepared update query
            $this->crudInstance->save($params, false, $pdoTypes);
            // Run unprepared update query 4d
            // @TODO
            if ($this->is4d()) {
                $paramsU = $this->getParams();
                $this->runUnpreparedFourdSaveQueries($paramsU, $pdoTypes, $timeFields, $booleansFields, $datesFields, $realFields);
            }
        }
        if ($id) {
            $this->crudInstance->find([], [$this->getPkName() => $id]);
            $result = $this->crudInstance->getRowsetAsArray();
            $datas = ($result) ? $result[0] : [];
            if ($this->is4d()) {
                $this->transfoFourdDatas($datas, $timeFields, $booleansFields);
            }
        }
        $form = new crudEditForm($datas, $this->fields, $this->adapter);
        if ($isModeDetail) {
            $form->setMode('readonly');
            $form->setEnableButtons(false);
        }
        $form->render();
        $title = ($mode) ? 'Detail' : 'Edition';
        $glyph = ($mode) ? glyphHelper::EYE_OPEN : glyphHelper::PENCIL;
        $widgetTitle = glyphHelper::get($glyph)
                . $title . ' ' . $this->table
                . $this->getEditLinks($mode);

        $widget = $this->getWidget($widgetTitle, (string) $form);
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * prepareSaveFourdDatas
     *
     * @param array $allowedParams
     * @param array $pdoTypes
     * @param array $timeFields
     * @param array $booleansFields
     * @param array $datesFields
     * @param array $realFields
     */
    private function prepareSaveFourdDatas(array &$allowedParams, array $pdoTypes, array $timeFields, array $booleansFields, array $datesFields, array $realFields)
    {
        $params = $allowedParams;
        foreach ($params as $k => $v) {
            $type = $pdoTypes[$k];
            if ($type === \PDO::PARAM_INT) {
                $params[$k] = (int) $params[$k];
            } elseif ($type === \PDO::PARAM_STR && !in_array($k, $realFields)) {
                $params[$k] = $this->getUtf8To16($params[$k]);
            }
        }
        $allowedParams = [];
        $allowedKeys = array_keys($params);
        $forbiden = array_merge($timeFields, $booleansFields, $datesFields, $realFields);
        $count = count($allowedKeys);
        for ($c = 0; $c < $count; ++$c) {
            $k = $allowedKeys[$c];
            if (!in_array($k, $forbiden)) {
                $allowedParams[$k] = $params[$k];
            }
        }
    }

    /**
     * transfoFourdDatas
     *
     * @param array $datas
     * @param array $timeFields
     * @param array $booleansFields
     */
    private function transfoFourdDatas(array &$datas, array $timeFields, array $booleansFields)
    {
        foreach ($datas as $key => $value) {
            if (in_array($key, $timeFields)) {
                $datas[$key] = $this->getTimeFromMs($value);
            } elseif (in_array($key, $booleansFields)) {
                $datas[$key] = ($datas[$key] === '1') ? 'TRUE' : 'FALSE';
            }
        }
    }

    /**
     * runUnpreparedFourdSaveQueries
     *
     * @param array $pdoTypes
     * @param array $timeFields
     * @param array $booleansFields
     * @param array $datesFields
     * @param array $realFields
     */
    private function runUnpreparedFourdSaveQueries(array $params, array $pdoTypes, array $timeFields, array $booleansFields, array $datesFields, array $realFields)
    {
        $pkValue = $params[$this->getPkName()];
        foreach ($params as $key => $value) {
            if (in_array($key, $timeFields)) {
                $sql = $this->getUnprepSqlUpdateQuery($pkValue, $key, $value);
                $this->crudInstance->cleanRowset()->run($sql);
            } elseif (in_array($key, $booleansFields)) {
                //$datas[$key] = ($datas[$key] === '1') ? 'TRUE' : 'FALSE';
            }
        }
    }

    /**
     * is4d
     *
     * @return bool
     */
    private function is4d(): bool
    {
        return $this->adapter === \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D;
    }

    /**
     * detail
     *
     * @return Pimvc\Http\Response
     */
    final public function detail()
    {
        $detailId = $this->getParams(self::_ID);
        $redirectUrl = $this->baseUrl . '/crud/edit/id/' . $detailId . '/mode/detail';
        return $this->redirect($redirectUrl);
    }

    /**
     * delete
     *
     * @return Pimvc\Http\Response
     */
    final public function delete()
    {
        $redirectUrl = $this->baseUrl . '/crud/manage';
        if ($id = $this->getParams(self::_ID)) {
            $forcedTypes = $this->crudInstance->getDomainInstance()->getPdos();
            $pkName = $this->getPkName();
            $pkType = $forcedTypes[$pkName];
            if ($this->is4d()) {
                $pkValue = ($pkType === \PDO::PARAM_STR) ? (string) $this->getCharsetConvert($id) : (int) $id;
            } else {
                $pkValue = ($pkType === \PDO::PARAM_STR) ? (string) $id : (int) $id;
            }

            $this->crudInstance->cleanRowset()
                    ->find([$pkName], [$pkName => $pkValue])
                    ->delete($forcedTypes);
            $hasError = $this->crudInstance->hasError();
            $messageType = ($hasError) ? flashTools::FLASH_ERROR : flashTools::FLASH_SUCCESS;
            $message = ($hasError) ? 'Crud error delete ' . $this->crudInstance->getError() : 'Crud deleted ' . $id;
            flashTools::add($messageType, $message);
        }
        return $this->redirect($redirectUrl);
    }

    /**
     * duplicate
     *
     * return Pimvc\Http\Response
     */
    final public function duplicate()
    {
        $redirectUrl = $this->baseUrl . '/crud/manage';
        if ($id = $this->getParams(self::_ID)) {
            $userObject = $this->crudInstance->getById($id);
            unset($userObject->id);
            $this->crudInstance->save($userObject, true);
            flashTools::addInfo('Crud id ' . $id . ' dupliqué.');
        }
        return $this->redirect($redirectUrl);
    }
}
