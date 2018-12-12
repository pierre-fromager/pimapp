<?php
/**
 * App1\Controller\Metro\Stations
 *
 * @author Pierre Fromager
 */
namespace App1\Controller\Metro;

use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Flash as flashTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \App1\Form\Metro\Stations\Search as searchMetroStationsForm;
use \App1\Form\Metro\Stations\Edit as editMetroStationsForm;
use \App1\Views\Helpers\Form\Search\Filter as formFilter;
use \App1\Helper\Controller\Metro\Stations as ControlerMetroStationsHelper;

final class Stations extends ControlerMetroStationsHelper
{

    /**
     * index
     *
     * @return \Pimvc\Http\Response
     */
    final public function index()
    {
        return $this->redirect($this->baseUrl . '/stations/manage');
    }

    /**
     * manage
     *
     * return Response
     */
    final public function manage()
    {
        $this->setPageSize();
        $criterias = $this->getAssist();
        $form = new searchMetroStationsForm($criterias);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get((string) $form);
        unset($form);
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::SEARCH) . 'Gestion stations métro',
            $filter . (string) $this->getListe($criterias)
        );
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * duplicate
     *
     * return Response
     */
    final public function duplicate()
    {
        if ($id = $this->getParams(self::PARAM_ID)) {
            $userObject = $this->lignesModel->getById($id);
            unset($userObject->id);
            $this->lignesModel->save($userObject);
            flashTools::addInfo('Station id ' . $id . ' dupliqué.');
            $redirectUrl = $this->baseUrl . '/' . self::LIST_ACTION;
            return $this->redirect($redirectUrl);
        }
        $this->getError();
    }

    /**
     * editAction
     *
     * @return Response
     */
    final public function edit()
    {
        $message = '';
        $this->stationsModel->cleanRowset();
        $isPost = $this->isPost();
        $isAdmin = sessionTools::isAdmin();
        $postedDatas = ($isPost) ? $this->getParams() : (array) $this->stationsModel->getById($this->getParams('id'));
        $form = new editMetroStationsForm($postedDatas, $mode = '');
        if ($isPost) {
            if ($form->isValid()) {
                $domainInstance = $this->lignesModel->getDomainInstance();
                $domainInstance->hydrate($postedDatas);
                $this->lignesModel->saveDiff($domainInstance);
                unset($domainInstance);
                if ($this->lignesModel->hasError()) {
                    $message = self::STATIONS_MESSAGE_EDIT_ERROR . $this->_model->getError();
                    return array(self::PARAM_CONTENT => $message);
                } else {
                    $redirectÀction = ($isAdmin) ? self::LIST_ACTION : self::DETAIL_ACTION;
                    flashTools::addInfo(self::STATIONS_MESSAGE_EDIT_VALIDATED);
                    return $this->redirect($this->baseUrl . '/' . $redirectÀction);
                }
            } else {
                foreach ($form->getErrors() as $k => $v) {
                    flashTools::addError($v);
                }
                $message = (string) $form;
            }
        } else {
            $message = (string) $form;
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::PENCIL) . 'Edition station',
            (string) $message
        );
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * detailAction
     *
     * @return Response
     */
    final public function detail()
    {
        $this->setDetailOsmAssets();
        $this->stationsModel->cleanRowset();
        $id = $this->getParams($this->stationsModel->getPrimary());
        $formDatas = $this->stationsModel->getById($id);
        $form = new \App1\Form\Metro\Stations\Edit($formDatas, $id, 'readonly');
        $form->setEnableButtons(false);
        $form->render();
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::EYE_OPEN)
                . 'Détail station',
            (string) $form . $this->detailMapOsm($formDatas)
        );
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * deleteAction
     *
     * @return array
     */
    final public function delete()
    {
        if ($this->hasValue(self::PARAM_ID)) {
            $this->lignesModel->cleanRowset();
            $where = [self::PARAM_ID => $this->getParams(self::PARAM_ID)];
            $this->stationsModel->setWhere($where);
            $this->stationsModel->bindWhere();
            $this->stationsModel->delete();
            $hasError = $this->stationsModel->hasError();
            $messageType = ($hasError) ? 'error' : 'info';
            $message = ($hasError) ? self::STATIONS_MESSAGE_DELETE_ERROR . $this->stationsModel->getError() : self::STATIONS_MESSAGE_DELETE_SUCCESS;
            flashTools::add($messageType, $message);
            return $this->redirect($this->baseUrl . '/' . self::LIST_ACTION);
        }
        $this->getError();
    }

    /**
     * criteriasjson
     *
     * @return Response
     */
    final public function criteriasjson()
    {
        return $this->getJsonResponse($this->getAssist());
    }
}
