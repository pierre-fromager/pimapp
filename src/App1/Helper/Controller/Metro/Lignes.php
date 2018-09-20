<?php
/**
 * Description of App1\Helper\Controller\Metro\Lignes
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller\Metro;

use \Pimvc\Input\Filter as inputFilter;
use \Pimvc\Input\Custom\Filters\Range as inputRange;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Helper\Math\Geo\Center as geoCenter;
use \Pimvc\Helper\Math\Graph\Pathes\Minpath as GraphMinpath;
use \Pimvc\Helper\Math\Graph\Pathes\Weighted as GraphWeighted;
use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Views\Helpers\Gis\Osm\Marker\Icon as OsmMarkerIcon;
use \Pimvc\Views\Helpers\Gis\Osm\Map as OsmMap;
use \Pimvc\Views\Helpers\Gis\Osm\Options as OsmMapOptions;
use \Pimvc\Views\Helpers\Gis\Osm\Marker\Options as OsmMarkerOptions;
use \Pimvc\Views\Helpers\Gis\Osm\Marker as OsmMarker;
use App1\Model\Metro\Lignes as modelLignes;
use App1\Model\Metro\Stations as modelStations;

class Lignes extends basicController
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
    const PARAM_ICON = 'icon';
    const PARAM_LINK = 'link';
    const WILDCARD = '%';
    const PHP_EXT = '.php';
    const LAYOUT_NAME = 'responsive';
    const PARAM_CONTENT = 'content';
    const USER_MESSAGE_DISCONECTED = 'Vous êtes déconnecté.';
    const PARAM_PAGESIZE = 'pagesize';
    const ERP_ASSIST_USER = 'metroLignes';
    const ERP_ASSIST_LIGNES = 'assist-metro-lignes';
    const PARAM_RESET = 'reset';
    const AJAX_TERM = 'term';
    const LIST_ACTION = '/metro/lignes/manage';
    const DETAIL_ACTION = '/metro/lignes/detail';
    const SEARCH_ACTION = '/metro/lignes/search';
    const USER_MESSAGE_VALDATED = 'Mise à jour effectuée.';
    const USER_MESSAGE_REGISTRATION_INVALID = 'Les champs obligatoires n\'ont pas été saisis correctement.';
    const FORM_INCOMPLETE_MESSAGE = 'Formulaire incomplet.';
    const USER_MESSAGE_DELETE_SUCCESS = 'Enregistrement supprimé';
    const _URI_STATION_MANAGE = '/metro/stations/manage';
    const _HSRC = modelLignes::_HSRC;
    const _HDST = modelLignes::_HDST;
    const _ICON_GIS = '/public/img/gis/icon/pin.png';

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
        $this->lignesModel = new \App1\Model\Metro\Lignes($this->modelConfig);
        $this->stationsModel = new \App1\Model\Metro\Stations($this->modelConfig);
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->setAssets();
    }

    /**
     * searchMapOsm
     *
     * @todo match zoom with distance
     * @param array $datas
     * @return string
     */
    protected function searchMapOsm(array $hCollection, $staInfos, $distance)
    {
        $markers = [];
        $markerIcon = new OsmMarkerIcon();
        $markerIcon->iconUrl = $this->baseUrl . self::_ICON_GIS;
        foreach ($hCollection as $h) {
            $sta = $this->stationsModel->getByH($h);
            $markerOptDep = new OsmMarkerOptions($markerIcon);
            $markerOptDep->title = $sta[modelStations::_NAME];
            $markerOptDep->alt = $sta[modelStations::_NAME];
            //$markerOptDep-> = $sta[modelStations::_NAME];
            $markerDep = new OsmMarker($markerOptDep);
            $markerDep->setLatlng($sta[modelStations::_LAT], $sta[modelStations::_LON]);
            $markers[] = $markerDep;
        }

        $markCenters = [];
        foreach ($markers as $marker) {
            $markCenters[] = [$marker->getLat(), $marker->getLng()];
        }

        $center = geoCenter::getFromAzimuts($markCenters);
        $mapOptions = new OsmMapOptions($center[0], $center[1]);
        $mapOptions->zoom = 14;
        $map = new OsmMap($this->baseUrl, $markers, $mapOptions);
        $map->setLayer($this->baseUrl . '/metro/lignes/tiles/s/{s}/z/{z}/x/{x}/y/{y}');
        $map->render();
        return (string) $map;
    }

    /**
     * searchWeighted
     *
     * @return array
     */
    protected function searchWeighted()
    {
        $ts = (float) microtime(true);
        $hsrc = $this->getParams(modelLignes::_HSRC);
        $hdst = $this->getParams(modelLignes::_HDST);
        $graphWeightedPath = new GraphWeighted($this->lignesModel->weightedNodes());
        return [
            modelLignes::_HSRC => $hsrc,
            modelLignes::_HDST => $hdst,
            'method' => 'weighted',
            'hops' => $graphWeightedPath->path($hsrc, $hdst),
            'distance' => $graphWeightedPath->distance(),
            'ellapse' => microtime(true) - $ts
        ];
    }

    /**
     * searchUnweighted
     *
     * @return array
     */
    protected function searchUnweighted()
    {
        $ts = (float) microtime(true);
        $hsrc = $this->getParams(modelLignes::_HSRC);
        $hdst = $this->getParams(modelLignes::_HDST);
        $graphMinPath = new GraphMinpath($this->lignesModel->adjacence());
        $p = $graphMinPath->path($hsrc, $hdst);
        $d = 0;
        $hStations = $this->stationsModel->getByH();
        $hCollection = array_keys($hStations);
        $distances = $this->lignesModel->kmDistances($hCollection);
        for ($cn = 0; $cn < count($p) - 1; $cn++) {
            $s = array_slice($p, $cn, 2);
            $d += $distances[$s[0]][$s[1]];
        }
        unset($graphMinPath);
        unset($hStations);
        unset($hCollection);
        unset($distances);
        return [
            modelLignes::_HSRC => $hsrc,
            modelLignes::_HDST => $hdst,
            'method' => 'unweighted',
            'hops' => $p,
            'distance' => $d,
            'ellapse' => microtime(true) - $ts
        ];
    }

    /**
     * detailButtons
     *
     * @return string
     */
    protected function detailButtons()
    {
        $readyLink = $this->hasValue(self::PARAM_ID) && sessionTools::isAdmin();
        $linkEditId = ($readyLink) ? '/id/' . $this->getParams(self::PARAM_ID) : '';
        $editButton = (sessionTools::isAdmin()) ? glyphHelper::getLinked(
            glyphHelper::PENCIL,
            $this->baseUrl . '/metro/lignes/edit' . $linkEditId,
            [self::PARAM_TITLE => 'Edition ligne']
        ) : '';

        $manageButton = glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . self::LIST_ACTION . $linkEditId,
            [self::PARAM_TITLE => 'Recherche lignes']
        );

        $links = '<div style="float:right">'
            . $editButton . '&nbsp;' . $manageButton
            . '</div>';
        return $links;
    }

    /**
     * detailMapOsm
     *
     * @param array $datas
     * @return string
     */
    protected function detailMapOsm(array $datas)
    {
        $markers = [];
        $markerIcon = new OsmMarkerIcon();
        $markerIcon->iconUrl = $this->baseUrl . self::_ICON_GIS;

        $srcSta = $this->stationsModel->getByH($datas[modelLignes::_HSRC]);
        $markerOptDep = new OsmMarkerOptions($markerIcon);
        $markerOptDep->title = $srcSta[modelStations::_NAME];
        $markerOptDep->alt = $srcSta[modelStations::_NAME];

        $markerDep = new OsmMarker($markerOptDep);
        $markerDep->setLatlng($srcSta[modelStations::_LAT], $srcSta[modelStations::_LON]);

        $markers[] = $markerDep;

        $dstSta = $this->stationsModel->getByH($datas[modelLignes::_HDST]);
        $markerOptArr = new OsmMarkerOptions($markerIcon);
        $markerOptArr->title = $dstSta[modelStations::_NAME];
        $markerOptArr->alt = $dstSta[modelStations::_NAME];
        $markerArr = new OsmMarker($markerOptArr);
        $markerArr->setLatlng($dstSta[modelStations::_LAT], $dstSta[modelStations::_LON]);
        $markers[] = $markerArr;

        $markCenters = [];
        foreach ($markers as $marker) {
            $markCenters[] = [$marker->getLat(), $marker->getLng()];
        }
        $center = geoCenter::getFromAzimuts($markCenters);
        $mapOptions = new OsmMapOptions($center[0], $center[1]);
        $map = new OsmMap($this->baseUrl, $markers, $mapOptions);
        $map->setLayer($this->baseUrl . '/metro/lignes/tiles/s/{s}/z/{z}/x/{x}/y/{y}');
        $map->render();
        return (string) $map;
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
            , self::PARAM_ICON => 'fa fa-sign-out'
            , self::PARAM_LINK => $this->baseUrl . '/user/logout'
            ] : [
            self::PARAM_TITLE => 'Login'
            , self::PARAM_ICON => 'fa fa-sign-in'
            , self::PARAM_LINK => $this->baseUrl . '/user/login'
            ];

        $isAdmin = sessionTools::isAdmin();
        if ($isAdmin) {
            $items += [
                [
                    self::PARAM_TITLE => 'Acl'
                    , self::PARAM_ICON => 'fa fa-lock'
                    , self::PARAM_LINK => $this->baseUrl . '/acl/manage'
                ],
                [
                    self::PARAM_TITLE => 'Database'
                    , self::PARAM_ICON => 'fa fa-database'
                    , self::PARAM_LINK => $this->baseUrl . '/database/tablesmysql'
                ]
            ];
        }

        $freeItems = [
            [
                self::PARAM_TITLE => 'Stations'
                , self::PARAM_ICON => 'fa fa-subway'
                , self::PARAM_LINK => $this->baseUrl . self::_URI_STATION_MANAGE
            ],
            [
                self::PARAM_TITLE => 'Itinéraires'
                , self::PARAM_ICON => 'fa fa-subway'
                , self::PARAM_LINK => $this->baseUrl . self::SEARCH_ACTION
            ]
        ];
        $items = array_merge($items, $freeItems);

        if ($isAuth) {
            $authItems = [];
            $items = array_merge($items, $authItems);
        }

        array_push($items, $authLink);
        $navConfig = [
            self::PARAM_TITLE => [
                'text' => 'Pimapp',
                self::PARAM_ICON => 'fa fa-home',
                self::PARAM_LINK => $this->baseUrl
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
            self::ERP_ASSIST_LIGNES,
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
            'chosen.css', 'form_responsive.css', 'main.css',
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
            self::PARAM_EMAIL => FILTER_SANITIZE_STRING
            ]
        );
    }

    /**
     * tile
     *
     * @return string
     */
    protected function tile($s, $x, $y, $z)
    {
        $cacheFile = $this->getApp()->getPath() . "cache/img/gis/osm/metro/$z/$x/$y.png";
        $cacheDir = dirname($cacheFile);
        $hasCacheDir = file_exists($cacheDir);
        $hasCacheFile = file_exists($cacheFile);
        if (!$hasCacheDir) {
            mkdir($cacheDir, 0777, true);
        }
        if (!$hasCacheFile) {
            file_put_contents($cacheFile, $this->downloadOsmMap($s, $x, $y, $z));
        }
        return $this->baseUrl . "/public/img/gis/osm/$z/$x/$y.png";
    }

    /**
     * downloadOsmMap
     *
     * @param int $s
     * @param int $x
     * @param int $y
     * @param int $z
     * @return string
     */
    protected function downloadOsmMap($s, $x, $y, $z)
    {
        if ($s && $z && $x && $y) {
            $client = new \GuzzleHttp\Client();
            $url = "https://{$s}.tile.openstreetmap.org/{$z}/{$x}/{$y}.png";
            $resOpts = ['auth' => ['user', 'pass']];
            $res = $client->request('GET', $url, $resOpts);
            $content = $res->getBody();
            unset($res);
            return $content;
        }
        return '';
    }
}
