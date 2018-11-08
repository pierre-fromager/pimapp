<?php

/**
 * Description of App1\Controller\Metro\Lignes
 *
 * @author Pierre Fromager
 */

namespace App1\Controller\Metro;

use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Flash as flashTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Views\Helpers\Fa as faHelper;
use \Pimvc\Views\Helpers\Widgets\Standart as widgetHelper;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use App1\Form\Metro\Lignes\Search as searchMetroLignesForm;
use App1\Form\Metro\Lignes\Edit as editMetroLignesForm;
use App1\Form\Metro\Lignes\Itineraire as searchItiForm;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use App1\Views\Helpers\Bootstrap\Nav as bootstrapNav;
use \App1\Model\Metro\Lignes as modelLignes;
use \App1\Helper\Controller\Metro\Lignes as ControlerMetroLignesHelper;

final class Lignes extends ControlerMetroLignesHelper
{

    /**
     * user
     *
     * @return Response
     */
    final public function index()
    {
        /*
          $input = $this->getIndexInputFilter();
          $transform = new \stdClass();
          $transform->filter = $input->get();
          $transform->data = $this->lignesModel->find(
          [self::PARAM_ID, self::PARAM_EMAIL],
          [
          self::PARAM_ID . '#>' => (isset($input->id)) ? $input->id : 800
          , self::PARAM_EMAIL =>
          (isset($input->email)) ? self::WILDCARD . $input->email . self::WILDCARD : self::WILDCARD
          ]
          )->getRowset();
          unset($input); */
        //$transform = return $this->getJsonResponse($transform);
    }

    /**
     * manage
     *
     * return Response
     */
    final public function manage()
    {
        $hasContext = $this->hasValue('context');
        $this->setPageSize();
        $criterias = $this->getAssist();

        $form = new searchMetroLignesForm($criterias);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get((string) $form);
        unset($form);
        $excludeToolbarAction = array(
            glyphToolbar::EXCLUDE_DETAIL => false
            , glyphToolbar::EXCLUDE_IMPORT => true
            , glyphToolbar::EXCLUDE_NEWSLETTER => true
            , glyphToolbar::EXCLUDE_PDF => true
            , glyphToolbar::EXCLUDE_CLONE => false
            , glyphToolbar::EXCLUDE_PEOPLE => true
            , glyphToolbar::EXCLUDE_REFUSE => true
            , glyphToolbar::EXCLUDE_VALIDATE => true
        );
        $listeFields = [
            $this->lignesModel->getPrimary(),
            modelLignes::_SRC,
            modelLignes::_DST,
            modelLignes::_DIST
        ];
        $listeExclude = array_diff(
            $this->lignesModel->getDomainInstance()->getVars(),
            $listeFields
        );
        $liste = new \Pimvc\Liste(
            get_class($this->lignesModel),
            'metro/lignes/manage',
            $listeExclude,
            $excludeToolbarAction,
            $this->getParams('page'),
            $criterias,
            [],
            [
            self::PARAM_ORDER => 'desc',
                ]
        );
        if (!sessionTools::isAdmin()) {
            $whereConditions = ['key' => 'id', 'operator' => '>', 'value' => 0];
            $conditions = [
                glyphToolbar::EXCLUDE_EDIT => $whereConditions,
                glyphToolbar::EXCLUDE_CLONE => $whereConditions,
                glyphToolbar::EXCLUDE_DELETE => $whereConditions,
            ];
            $liste->setActionCondition($conditions);
        }
        $liste->setActionPrefix('lignes/');
        $liste->setLabels(editMetroLignesForm::_getStaticLabels($withIcons = false));
        if ($hasContext) {
            $this->getJsonHeaders();
            echo $liste->getJson();
            die;
        }
        $liste->setShowSql(false)->render();

        $stationsButton = glyphHelper::getLinked(
            glyphHelper::MAP_MARKER,
            $this->baseUrl . self::_URI_STATION_MANAGE,
            array(self::PARAM_TITLE => 'Gestion stations')
        );
        $widgetTitle = glyphHelper::get(glyphHelper::SEARCH)
                . 'Gestion lignes métro'
                . '<div style="float:right">' . $stationsButton
                . '</div>';
        $widget = (new widgetHelper())
                ->setTitle($widgetTitle)
                ->setBody(
                    $filter
                    . '<div class="table-responsive">' . (string) $liste . '</div>'
                );
        unset($liste);
        $widget->render();
        $content = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $content);
    }

    /**
     * search
     *
     * return Response
     */
    final public function search()
    {
        $this->setDetailOsmAssets();
        $openCrit = '<script>$j(\'#criteriaFilter\').click()</script>';
        $widgetContent = '<h3 class="text-center">'
                . 'Selectionner les critères de départ et d\'arrivée.'
                . '</h3>'
                . $openCrit;
        if ($this->getParams(self::_HSRC) && $this->getParams(self::_HDST)) {
            $hasContext = $this->hasValue('context');
            $isUnweighted = ($this->getParams(searchItiForm::_OPTIM) && $this->getParams(searchItiForm::_OPTIM) === 'unweighted');
            $r = ($isUnweighted) ? $this->searchUnweighted() : $this->searchWeighted();
            $hops = $r['hops'];
            $hs = count($hops);
            $hCouples = [];
            for ($c = 0; $c < $hs; ++$c) {
                $src = $hops[0];
                $dst = (isset($hops[1])) ? $hops[1] : '';
                if ($src && $dst) {
                    $hCouples[] = [$src, $dst];
                }
                array_shift($hops);
            }
            $stasCouples = [];
            foreach ($hCouples as $hCouple) {
                $rs = $this->lignesModel->getTroncon($hCouple[0], $hCouple[1]);
                if (!empty($rs)) {
                    $stasCouples[] = $rs;
                }
            }
            $tableData = [];
            $itiCount = 0;
            foreach ($stasCouples as $hCouple) {
                $itiCount++;
                $tableData[] = [$itiCount, $hCouple[modelLignes::_LIGNE],
                    $hCouple[modelLignes::_SRC], $hCouple[modelLignes::_DST],
                    round($hCouple[modelLignes::_DIST], 3)
                ];
            }
            $itiTable = new \Pimvc\Views\Helpers\Table(
                '',
                ['Dir', 'Ligne', 'Départ', 'Arrivée', 'Dist km'],
                $tableData
            );
            $itiTable->setId('itiTable');
            $itiTable->render();

            if ($hasContext) {
                $this->getJsonHeaders();
                echo \json_encode((object) $r, JSON_PRETTY_PRINT);
                die;
            }

            $widgetContent = $itiTable . '<br style="clear:both"/>'
                    . $this->searchMapOsm($r['hops'], $stasCouples, $r['distance']);
        }

        $widgetTitle = faHelper::get(faHelper::CUBES)
                . 'Recherche itinéraire'
                . '<div style="float:right">' . $this->detailButtons() . '</div>';
        $form = new searchItiForm($this->getParams());
        $widgetBody = '<div class="table-responsive">' . (string) $form . $widgetContent . '</div>';
        $widget = (new widgetHelper())->setTitle($widgetTitle)->setBody($widgetBody);
        $widget->render();
        $content = (string) $widget;
        unset($widget);
        unset($form);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $content);
    }

    /**
     * tiles
     *
     * @return response
     */
    final public function tiles()
    {
        $z = $this->getParams('z');
        $x = $this->getParams('x');
        $y = $this->getParams('y');
        $s = $this->getParams('s');

        $redirectUrl = $this->baseUrl . self::_ICON_GIS;
        if ($s && $z && $x && $y) {
            $redirectUrl = $this->tile($s, $x, $y, $z);
        }
        return $this->redirect($redirectUrl);
    }

    /**
     * rebuild
     *
     * return Response
     */
    final public function rebuild()
    {
        $redirectUrl = $redirectUrl = $this->baseUrl . '/' . self::LIST_ACTION;
        $this->stationsModel->rebuildh();
        $hStations = $this->stationsModel->getByH();
        $this->lignesModel->updateDistances($hStations);
        return $this->redirect($redirectUrl);
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
            flashTools::addInfo('Ligne id ' . $id . ' dupliqué.');
            $redirectUrl = $this->baseUrl . DIRECTORY_SEPARATOR . self::LIST_ACTION;
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
        $this->lignesModel->cleanRowset();
        $isPost = ($this->getApp()->getRequest()->getMethod() === 'POST');
        $isAdmin = sessionTools::isAdmin();
        $postedDatas = ($isPost) ? $this->getParams() : (array) $this->lignesModel->getById($this->getParams('id'));
        $form = new editMetroLignesForm($postedDatas, $mode = '');
        if ($isPost) {
            if ($form->isValid()) {
                $domainInstance = $this->lignesModel->getDomainInstance();
                $domainInstance->hydrate($postedDatas);
                $this->lignesModel->saveDiff($domainInstance);
                unset($domainInstance);
                $hasError = $this->lignesModel->hasError();
                if ($hasError) {
                    $message = self::USER_MESSAGE_ERROR . $this->_model->getError();
                    return array(self::PARAM_CONTENT => $message);
                } else {
                    $redirectÀction = ($isAdmin) ? self::LIST_ACTION : self::DETAIL_ACTION;
                    flashTools::addInfo(self::USER_MESSAGE_VALDATED);
                    return $this->redirect($this->baseUrl . $redirectÀction);
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
        $linkDetailId = ($this->hasValue(self::PARAM_ID)) ? '/id/' . $this->getParams(self::PARAM_ID) : '';
        $linkManage = ($isAdmin) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . self::LIST_ACTION,
            [self::PARAM_TITLE => 'Recherche de lignes']
        ) : '';
        $linkDetail = glyphHelper::getLinked(
            glyphHelper::EYE_OPEN,
            $this->baseUrl . self::DETAIL_ACTION . $linkDetailId,
            [self::PARAM_TITLE => 'Détail']
        );
        $links = '<div style="float:right">'
                . $linkManage
                . $linkDetail
                . '</div>';
        $widgetTitle = glyphHelper::get(glyphHelper::PENCIL)
                . 'Edition du tronçon' . $links;
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
        $this->lignesModel->cleanRowset();
        $id = $this->getParams('id');
        $formDatas = $this->lignesModel->getById($id);
        $form = new \App1\Form\Metro\Lignes\Edit(
            $formDatas,
            $id,
            $mode = 'readonly'
        );
        $form->setEnableButtons(false);
        $form->render();

        $widgetTitle = glyphHelper::get(glyphHelper::EYE_OPEN)
                . 'Détail du tronçon' . $this->detailButtons();
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
            $this->lignesModel->setWhere($where);
            $this->lignesModel->bindWhere();
            $this->lignesModel->delete();
            $hasError = $this->lignesModel->hasError();
            $messageType = ($hasError) ? 'error' : 'info';
            $message = ($hasError) ? self::USER_MESSAGE_DELETE_ERROR . $this->lignesModel->getError() : self::USER_MESSAGE_DELETE_SUCCESS;
            flashTools::add($messageType, $message);
            return $this->redirect($this->baseUrl . DIRECTORY_SEPARATOR . self::LIST_ACTION);
        }
        $this->getError();
    }
}
