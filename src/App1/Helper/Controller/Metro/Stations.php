<?php
/**
 * Description of App1\Helper\Controller\Metro\Stations
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller\Metro;

use \Pimvc\Input\Filter as inputFilter;
use \Pimvc\Input\Custom\Filters\Range as inputRange;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Views\Helpers\Gis\Osm\Marker\Icon as OsmMarkerIcon;
use \Pimvc\Views\Helpers\Gis\Osm\Map as OsmMap;
use \Pimvc\Views\Helpers\Gis\Osm\Options as OsmMapOptions;
use \Pimvc\Views\Helpers\Gis\Osm\Marker\Options as OsmMarkerOptions;
use \Pimvc\Views\Helpers\Gis\Osm\Marker as OsmMarker;
use \Pimvc\Controller\Basic as basicController;
use App1\Form\Metro\Stations\Edit as editMetroStationsForm;
use App1\Model\Metro\Lignes as modelLignes;
use App1\Model\Metro\Stations as modelStations;

class Stations extends basicController
{

    const PUBLIC_CSS = '/public/css/';
    const PUBLIC_JS = '/public/js/';
    const PARAM_ID = 'id';
    const PARAM_ORDER = 'order';
    const PARAM_NAME = 'name';
    const PARAM_STATUS = 'status';
    const PARAM_PROFIL = 'profil';
    const PARAM_TOKEN = 'token';
    const PARAM_EMAIL = 'email';
    const PARAM_LOGIN = 'login';
    const PARAM_PASSWORD = 'password';
    const PARAM_TITLE = 'title';
    const WILDCARD = '%';
    const PHP_EXT = '.php';
    const LAYOUT_NAME = 'responsive';
    const PARAM_CONTENT = 'content';
    const USER_MESSAGE_DISCONECTED = 'Vous êtes déconnecté.';
    const PARAM_PAGESIZE = 'pagesize';
    const ERP_ASSIST_STATIONS = 'assist-metro-stations';
    const PARAM_RESET = 'reset';
    const AJAX_TERM = 'term';
    const LIST_ACTION = 'metro/stations/manage';
    const DETAIL_ACTION = 'metro/stations/detail';
    const METRO_STATIONS_URI = '/metro/stations/';
    const STATIONS_MESSAGE_EDIT_ERROR = 'Erreur mise à jour.';
    const STATIONS_MESSAGE_EDIT_VALIDATED = 'Mise à jour effectuée.';
    const FORM_INCOMPLETE_MESSAGE = 'Formulaire incomplet.';
    const STATIONS_MESSAGE_DELETE_SUCCESS = 'Enregistrement supprimé';
    const STATIONS_MESSAGE_DELETE_ERROR = 'Problème suppression';

    protected $modelConfig;
    protected $lignesModel;
    protected $stationsModel;
    protected $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->modelConfig = $this->getApp()->getConfig()->getSettings('dbPool');
        $this->lignesModel = new modelLignes($this->modelConfig);
        $this->stationsModel = new modelStations($this->modelConfig);
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->setAssets();
    }

    /**
     * getListe
     *
     * @param array $criterias
     * @return \Pimvc\Liste
     */
    protected function getListe($criterias)
    {
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
            $this->stationsModel->getPrimary(),
            modelStations::_NAME,
            modelLignes::_LAT,
            modelLignes::_LON
        ];
        $listeExclude = array_diff(
            $this->stationsModel->getDomainInstance()->getVars(),
            $listeFields
        );
        $liste = new \Pimvc\Liste(
            get_class($this->stationsModel),
            self::LIST_ACTION,
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
            $whereConditions = [
                'key' => $this->stationsModel->getPrimary(),
                'operator' => '>',
                'value' => 0
            ];
            $conditions = [
                glyphToolbar::EXCLUDE_EDIT => $whereConditions,
                glyphToolbar::EXCLUDE_CLONE => $whereConditions,
                glyphToolbar::EXCLUDE_DELETE => $whereConditions,
            ];
            $liste->setActionCondition($conditions);
        }
        $liste->setActionPrefix('stations/');
        $liste->setLabels(editMetroStationsForm::_getStaticLabels($withIcons = false));
        if ($this->hasValue('context')) {
            $this->getJsonHeaders();
            echo $liste->getJson();
            die;
        }
        $liste->setShowSql(false)->render();
        return $liste;
    }

    /**
     * detailMapOsm
     *
     * @param array $formDatas
     * @return string
     */
    protected function detailMapOsm(array $datas)
    {
        $markerIcon = new OsmMarkerIcon();
        $markerIcon->iconUrl = $this->baseUrl . '/public/img/gis/icon/pin.png';
        $markerOptions = new OsmMarkerOptions($markerIcon);
        $markerOptions->title = $datas[modelStations::_NAME];
        $markerOptions->alt = $datas[modelStations::_NAME];
        $marker = new OsmMarker($markerOptions);
        $marker->setLatlng($datas[modelStations::_LAT], $datas[modelStations::_LON]);
        $markers = [];
        $markers[] = $marker;
        $mapOptions = new OsmMapOptions($marker->getLat(), $marker->getLng());
        $map = new OsmMap($this->baseUrl, $markers, $mapOptions);
        $map->render();
        return (string) $map;
    }

    /**
     * detailButtons
     *
     * @return string
     */
    protected function detailButtons()
    {
        $shouldEdit = ($this->hasValue(self::PARAM_ID) && sessionTools::isAdmin());
        $linkEditId = ($shouldEdit) ? '/id/' . $this->getParams(self::PARAM_ID) : '';
        $editButton = (sessionTools::isAdmin()) ? glyphHelper::getLinked(
            glyphHelper::PENCIL,
            $this->stationsUrl('edit') . $linkEditId,
            [self::PARAM_TITLE => 'Edition station']
        ) : '';

        $manageButton = glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->stationsUrl('manage') . $linkEditId,
            [self::PARAM_TITLE => 'Recherche stations']
        );

        $links = '<div style="float:right">'
            . $editButton . '&nbsp;' . $manageButton
            . '</div>';
        return $links;
    }

    /**
     * linkDetail
     *
     * @param int $id
     * @return string
     */
    protected function linkDetail($id = '')
    {
        $linkDetailId = ($id) ? '/id/' . $this->getParams(self::PARAM_ID) : '';
        return glyphHelper::getLinked(
            glyphHelper::EYE_OPEN,
            $this->stationsUrl('detail') . $linkDetailId,
            [self::PARAM_TITLE => 'Détail station']
        );
    }

    /**
     * linkManage
     *
     * @return string
     */
    protected function linkManage()
    {
        return glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->stationsUrl('manage'),
            [self::PARAM_TITLE => 'Recherche de stations']
        );
    }

    /**
     * getLayout
     *
     * @param string $content
     * @return \App1\Views\Helpers\Layouts\Responsive
     */
    protected function getLayout($content)
    {
        $layout = (new \App1\Views\Helpers\Layouts\Responsive());
        $layoutParams = ['content' => $content];
        $layout->setApp($this->getApp())
            ->setName(self::LAYOUT_NAME)
            ->setLayoutParams($layoutParams)
            ->build();
        return $layout;
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    protected function getNavConfig()
    {
        $items = [];
        $isAuth = sessionTools::isAuth();
        $isPro = sessionTools::getProfil() === 'pro';
        $authLink = ($isAuth) ? [
            self::PARAM_TITLE => 'Logout'
            , 'icon' => 'fa fa-sign-out'
            , 'link' => $this->baseUrl . '/user/logout'
            ] : [
            self::PARAM_TITLE => 'Login'
            , 'icon' => 'fa fa-sign-in'
            , 'link' => $this->baseUrl . '/user/login'
            ];

        $freeItems = [
            [
                'title' => 'Lignes'
                , 'icon' => 'fa fa-subway'
                , 'link' => $this->baseUrl . '/metro/lignes/manage'
            ], [
                'title' => 'Itinéraires'
                , 'icon' => 'fa fa-subway'
                , 'link' => $this->baseUrl . '/metro/lignes/search'
            ]
        ];

        $items += $freeItems;

        $isAdmin = sessionTools::isAdmin();
        if ($isAdmin) {
            $items += [
                [
                    self::PARAM_TITLE => 'Acl'
                    , 'icon' => 'fa fa-lock'
                    , 'link' => $this->baseUrl . '/acl/manage'
                ],
                [
                    self::PARAM_TITLE => 'Database'
                    , 'icon' => 'fa fa-database'
                    , 'link' => $this->baseUrl . '/database/tablesmysql'
                ], [
                    self::PARAM_TITLE => 'Stations'
                    , 'icon' => 'fa fa-subway'
                    , 'link' => $this->baseUrl . '/metro/stations/manage'
                ]
            ];
        }

        if ($isAuth) {
            $authItems = [/* [
                  self::PARAM_TITLE => 'Bizz Calc'
                  , 'icon' => 'fa fa-calculator'
                  , 'link' => $this->baseUrl . '/business/index'
                  ],[
                  self::PARAM_TITLE => 'Bizz Cra'
                  , 'icon' => 'fa fa-calendar'
                  , 'link' => $this->baseUrl . '/business/calendar'
                  ] */];
            $items = array_merge($items, $authItems);
        }


        array_push($items, $authLink);
        $navConfig = [
            self::PARAM_TITLE => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-home',
                'link' => $this->baseUrl
            ],
            'items' => $items
        ];
        return $navConfig;
    }

    /**
     * setPageSize
     *
     */
    protected function setPageSize()
    {
        if ($this->getParams(self::PARAM_PAGESIZE)) {
            sessionTools::set(
                self::PARAM_PAGESIZE,
                $this->getParams(self::PARAM_PAGESIZE)
            );
        }
    }

    /**
     * getAssist
     *
     * @return array
     */
    protected function getAssist()
    {
        return sessionAssistTools::getSearch(
            self::ERP_ASSIST_STATIONS,
            $this->getApp()->getRequest(),
            $this->getParams(self::PARAM_RESET)
        );
    }

    /**
     * setAssets
     *
     */
    protected function setAssets()
    {
        $cssAssets = [
            'widget.css', 'tables/table-6.css', 'jquery.selectbox.css',
            'chosen.css', 'form_responsive.css',
            'bootstrap/datepicker.css'
        ];
        for ($c = 0; $c < count($cssAssets); $c++) {
            cssCollection::add(self::PUBLIC_CSS . $cssAssets[$c]);
        }
        cssCollection::save();
        $jsAssets = [
            'sortable.js', 'chosen.jquery.js', 'jquery.autogrow.js',
            'jquery.columnmanager.js', 'jquery.cookie.js',
            'vendor/bootstrap-datepicker.js'
        ];
        for ($c = 0; $c < count($jsAssets); $c++) {
            jsCollection::add(self::PUBLIC_JS . $jsAssets[$c]);
        }
        jsCollection::save();
    }

    /**
     * setDetailOsmAssets
     *
     */
    protected function setDetailOsmAssets()
    {
        $cssAssets = [
            'leaflet.css',
            'L.Icon.FontAwesome.css',
            'leaflet/plugins/fullscreen/leaflet.fullscreen.css',
            'leaflet/plugins/zoom/display.css',
        ];
        for ($c = 0; $c < count($cssAssets); $c++) {
            cssCollection::add(self::PUBLIC_CSS . $cssAssets[$c]);
        }
        cssCollection::save();
        $jsAssets = [
            'leaflet.js',
            'L.Icon.FontAwesome.js',
            'leaflet/plugins/fullscreen/Leaflet.fullscreen.js',
            'leaflet/plugins/mouse/position.js',
            'leaflet/plugins/zoom/display.js',
        ];
        for ($c = 0; $c < count($jsAssets); $c++) {
            jsCollection::add(self::PUBLIC_JS . $jsAssets[$c]);
        }
        jsCollection::save();
    }

    /**
     * getIndexInputFilter
     *
     * @return inputFilter
     */
    protected function getIndexInputFilter()
    {
        return new inputFilter(
            $this->getParams(),
            [
            self::PARAM_ID => new inputRange([
                inputRange::MIN_RANGE => 1,
                inputRange::MAX_RANGE => 10000,
                inputRange::_DEFAULT => 800,
                inputRange::CAST => inputRange::FILTER_INTEGER
                ]),
            modelStations::_H => FILTER_SANITIZE_STRING
            ]
        );
    }

    /**
     * getFarthest
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    protected function getFarthest($lat, $lon)
    {
        return $this->stationsModel->getFarthest($lat, $lon);
    }

    /**
     * getClosest
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    protected function getClosest($lat, $lon)
    {
        return $this->stationsModel->getClosest($lat, $lon);
    }

    /**
     * stationsUri
     *
     * @param string $action
     * @return string
     */
    private function stationsUri($action = '')
    {
        return self::METRO_STATIONS_URI . $action;
    }


    /**
     * stationsUrl
     *
     * @param string $action
     * @return string
     */
    private function stationsUrl($action = '')
    {
        return $this->baseUrl . $this->stationsUri($action);
    }
}
