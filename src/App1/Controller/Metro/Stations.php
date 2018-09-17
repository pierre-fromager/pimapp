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
use \Pimvc\Views\Helpers\Widgets\Standart as widgetHelper;
use App1\Form\Metro\Stations\Search as searchMetroStationsForm;
use App1\Form\Metro\Stations\Edit as editMetroStationsForm;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use App1\Views\Helpers\Bootstrap\Nav as bootstrapNav;
use App1\Helper\Controller\Metro\Stations as ControlerMetroStationsHelper;

final class Stations extends ControlerMetroStationsHelper
{

    /**
     * user
     *
     * @return \Pimvc\Http\Response
     */
    final public function index()
    {
        /*
          $input = $this->getIndexInputFilter();
          $transform = new \stdClass();
          $transform->filter = $input->get();
          $where = [];
          if (isset($input->id)) {
          $where[self::PARAM_ID] = $input->id;
          }
          if (isset($input->h)) {
          $where['h'] = $input->h;
          }
          $transform->data = $this->stationsModel
          ->find([], $where)
          ->getRowset();
          unset($input); */
        $lat = 48.853;
        $lon = 2.35;
        $transform = $this->getFarthest($lat, $lon);
        return $this->getJsonResponse($transform);
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
        $widgetTitle = glyphHelper::get(glyphHelper::SEARCH)
                . 'Gestion stations métro';
        $widget = (new widgetHelper())
            ->setTitle($widgetTitle)
            ->setBody(
                $filter
                . '<div class="table-responsive">'
                . (string) $this->getListe($criterias)
                . '</div>'
            );
        $widget->render();
        $content = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $content);
    }

    /**
     * duplicate
     *
     * return Response
     */
    final public function duplicate()
    {
        $id = $this->getParams(self::PARAM_ID);
        if ($id) {
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
        $isPost = ($this->getApp()->getRequest()->getMethod() === 'POST');
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

        $links = '<div style="float:right">'
            . $this->linkManage()
            . $this->linkDetail($this->getParams(self::PARAM_ID))
            . '</div>';
        $widgetTitle = glyphHelper::get(glyphHelper::PENCIL)
                . 'Edition station' . $links;
        $widget = (new widgetHelper())->setTitle($widgetTitle)->setBody((string) $message);
        $widget->render();
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $widget);
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
        $form = new \App1\Form\Metro\Stations\Edit(
            $formDatas,
            $id,
            $mode = 'readonly'
        );
        $form->setEnableButtons(false);
        $form->render();

        $widgetTitle = glyphHelper::get(glyphHelper::EYE_OPEN)
                . 'Détail station' . $this->detailButtons();
        $widget = (new widgetHelper())
            ->setTitle($widgetTitle)
            ->setBody((string) $form . $this->detailMapOsm($formDatas));
        $widget->render();
        $detailContent = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . $detailContent);
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
