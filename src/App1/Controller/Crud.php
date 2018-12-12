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
            (string) $form . $table
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
        $fieldList = array_map(function (modelField $v) {
            return $v->getName();
        }, iterator_to_array($this->fields));
        $criterias = $this->getSearchAssist();
        $form = new crudSearchForm($criterias, $fieldList);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get((string) $form);
        unset($form);
        $liste = $this->getListe($this->crudInstance, $criterias, $fieldList);
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
        $datas = [];
        if ($this->isPost() && !$isModeDetail) {
            $this->crudInstance->save($this->getParams());
        }
        if ($id) {
            $this->crudInstance->find([], [self::_ID => $id]);
            $result = $this->crudInstance->getRowsetAsArray();
            $datas = ($result) ? $result[0] : [];
        }
        $form = new crudEditForm($datas, $fieldList);
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
     * detail
     *
     * @return Pimvc\Http\Response
     */
    final public function detail()
    {
        $redirectUrl = $this->baseUrl . '/crud/edit/id/'
            . $this->getParams(self::_ID)
            . '/mode/detail';
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
            $this->crudInstance->cleanRowset();
            $this->crudInstance->setWhere([self::_ID => $id]);
            $this->crudInstance->bindWhere();
            $this->crudInstance->delete();
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
