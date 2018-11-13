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
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use App1\Form\Metro\Lignes\Search as searchMetroLignesForm;
use App1\Form\Metro\Lignes\Edit as editMetroLignesForm;
use App1\Form\Metro\Lignes\Itineraire as searchItiForm;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use \App1\Model\Metro\Lignes as modelLignes;
use \App1\Helper\Controller\Metro\Lignes as ControlerMetroLignesHelper;

final class Lignes extends ControlerMetroLignesHelper
{

    /**
     * manage
     *
     * return Response
     */
    final public function manage()
    {
        $this->setPageSize();
        $criterias = $this->getAssist(
            ControlerMetroLignesHelper::ERP_ASSIST_LIGNES
        );
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
        if ($this->hasValue('context')) {
            return $this->getJsonResponse($liste->getJson());
        }
        $liste->setShowSql(false)->render();
        $stationsButton = glyphHelper::getLinked(
            glyphHelper::MAP_MARKER,
            $this->baseUrl . self::_URI_STATION_MANAGE,
            array(self::_TITLE => 'Gestion stations')
        );
        $widget = $this->getWidget(glyphHelper::get(glyphHelper::SEARCH)
                . 'Gestion lignes métro'
                . $this->getWidgetLinkWrapper($stationsButton), $filter
                . $this->getListeTableResponsive($liste));
        unset($form, $liste);
        return (string) $this->getLayout((string) $widget);
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
            if ($this->hasValue('context')) {
                return $this->getJsonResponse(
                    \json_encode((object) $r, JSON_PRETTY_PRINT)
                );
            }
            $widgetContent = $itiTable . '<br style="clear:both"/>'
                    . $this->searchMapOsm($r['hops'], $stasCouples, $r['distance']);
        }
        $form = new searchItiForm($this->getParams());
        $widget = $this->getWidget(faHelper::get(faHelper::CUBES)
                . 'Recherche itinéraire' . $this->getWidgetLinkWrapper(
                    $this->detailButtons()
                ), $this->getListeTableResponsive(
                    (string) $form . $widgetContent
                ));
        unset($form);
        return (string) $this->getLayout((string) $widget);
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
        $id = $this->getParams(self::_ID);
        if ($id) {
            $userObject = $this->lignesModel->getById($id);
            unset($userObject->id);
            $this->lignesModel->save($userObject);
            flashTools::addInfo('Line id ' . $id . ' dupliqué.');
            $redirectUrl = $this->baseUrl . DIRECTORY_SEPARATOR . self::LIST_ACTION;
            return $this->redirect($redirectUrl);
        }
        $this->getError();
    }

    /**
     * edit
     *
     * @return string
     */
    final public function edit()
    {
        $message = '';
        $this->lignesModel->cleanRowset();
        $isPost = $this->isPost();
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
                $formErrors = $form->getErrors();
                foreach ($formErrors as $k => $v) {
                    flashTools::addError($k . ':' . $v);
                }
                $message = (string) $form;
            }
        } else {
            $message = (string) $form;
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::PENCIL)
                . 'Edition du tronçon' . $this->getEditLinks(),
            (string) $message
        );
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * detail
     *
     * @return Response
     */
    final public function detail()
    {
        $this->setDetailOsmAssets();
        $this->lignesModel->cleanRowset();
        $id = $this->getParams('id');
        $formDatas = $this->lignesModel->getById($id);
        $form = new editMetroLignesForm($formDatas, $id, $mode = 'readonly');
        $form->setEnableButtons(false);
        $form->render();
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::EYE_OPEN)
                . 'Détail du tronçon'
                . $this->detailButtons(),
            (string) $form . $this->detailMapOsm($formDatas)
        );
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * delete
     *
     * @return array
     */
    final public function delete()
    {
        if ($this->hasValue(self::_ID)) {
            $this->lignesModel->cleanRowset();
            $where = [self::_ID => $this->getParams(self::_ID)];
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
