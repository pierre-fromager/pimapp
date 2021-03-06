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
use \App1\Model\Metro\Lignes as modelLignes;
use \App1\Model\Metro\Stations as modelStations;
use \App1\Helper\Format\Metro\Lignes\Colors as helperMetroLigneColors;
use \App1\Helper\Nav\Auto\Config as autoNavConfig;

class Lignes extends basicController
{

    use \App1\Helper\Reuse\Controller;

    const PUBLIC_CSS = '/public/css/';
    const PUBLIC_JS = '/public/js/';
    const _ID = 'id';
    const _PAGESIZE = 'pagesize';
    const PARAM_ORDER = 'order';
    const PARAM_ICON = 'icon';
    const PARAM_LINK = 'link';
    const WILDCARD = '%';
    const PHP_EXT = '.php';
    const LAYOUT_NAME = 'responsive';
    const PARAM_CONTENT = 'content';
    const ERP_ASSIST_LIGNES = 'assist-metro-lignes';
    const PARAM_RESET = 'reset';
    const AJAX_TERM = 'term';
    const LIST_ACTION = '/metro/lignes/manage';
    const DETAIL_ACTION = '/metro/lignes/detail';
    const SEARCH_ACTION = '/metro/lignes/search';
    const _URI_STATION_MANAGE = '/metro/stations/manage';
    const _HSRC = modelLignes::_HSRC;
    const _HDST = modelLignes::_HDST;
    const _ICON_GIS = '/public/img/gis/icon/pin.png';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';

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
            $markerDep = new OsmMarker($markerOptDep);
            $markerDep->setLatlng($sta[modelStations::_LAT], $sta[modelStations::_LON]);
            $markers[] = $markerDep;
        }

        $markCenters = [];
        foreach ($markers as $marker) {
            $markCenters[] = [$marker->getLat(), $marker->getLng()];
        }

        $polylines = [];
        for ($cp = 0; $cp < count($staInfos); $cp++) {
            $poly = new \stdClass();
            $polyOptions = new \stdClass();
            $polyOptions->color = helperMetroLigneColors::get($staInfos[$cp][modelLignes::_LIGNE]);
            $polyOptions->weight = 5;
            $polyOptions->opacity = 1;
            $polyOptions->smoothFactor = 1;
            $poly->tupple = $staInfos[$cp]['geo'];
            $poly->options = $polyOptions;
            $poly->title = $staInfos[$cp][modelLignes::_LIGNE];
            $polylines[] = $poly;
        }

        $center = geoCenter::getFromAzimuts($markCenters);
        $mapOptions = new OsmMapOptions($center[0], $center[1]);
        $mapOptions->zoom = 14;
        $mapOptions
                ->setBoundSouthWest([48.5516, 3.01025])
                ->setBoundNorthEast([49.17991, 1.69739]);
        $map = (new OsmMap())
                ->setLayer($this->baseUrl . '/metro/lignes/tiles/s/{s}/z/{z}/x/{x}/y/{y}')
                ->setMarkers($markers)
                ->setPolylines($polylines)
                ->setOptions($mapOptions)
                ->render();
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
        $readyLink = $this->hasValue(self::_ID) && sessionTools::isAdmin();
        $linkEditId = ($readyLink) ? '/id/' . $this->getParams(self::_ID) : '';
        $editButton = (sessionTools::isAdmin()) ? glyphHelper::getLinked(
            glyphHelper::PENCIL,
            $this->baseUrl . '/metro/lignes/edit' . $linkEditId,
            [self::_TITLE => 'Edition ligne']
        ) : '';

        $manageButton = glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . self::LIST_ACTION . $linkEditId,
            [self::_TITLE => 'Recherche lignes']
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

        $polylines = [];
        $poly = new \stdClass();
        $polyOptions = new \stdClass();
        $polyOptions->color = helperMetroLigneColors::get($datas[modelLignes::_LIGNE]);
        $polyOptions->weight = 5;
        $polyOptions->opacity = 1;
        $polyOptions->smoothFactor = 1;
        $poly->tupple = [
            [$srcSta[modelStations::_LAT], $srcSta[modelStations::_LON]],
            [$dstSta[modelStations::_LAT], $dstSta[modelStations::_LON]]
        ];
        $poly->options = $polyOptions;
        $poly->title = $datas[modelLignes::_LIGNE];
        $polylines[] = $poly;

        $center = geoCenter::getFromAzimuts($markCenters);
        $mapOptions = new OsmMapOptions($center[0], $center[1]);
        $mapOptions
                ->setBoundSouthWest([48.5516, 3.01025])
                ->setBoundNorthEast([49.17991, 1.69739]);
        $map = (new OsmMap())
                ->setLayer($this->baseUrl . '/metro/lignes/tiles/s/{s}/z/{z}/x/{x}/y/{y}')
                ->setMarkers($markers)
                ->setPolylines($polylines)
                ->setOptions($mapOptions)
                ->render();
        return (string) $map;
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    protected function getNavConfig(): array
    {
        $filter = [
            '(user)\/(.*)(ge|it|word|er)$',
            '(home)\/(.*)(board)$',
            '(metro)\/(lignes|stations)\/(.*)(ge|ch)$',
            '(crud)\/(.*)(ge)$',
        ];
        return (new autoNavConfig)->setFilter($filter)->render()->getConfig();
    }

    /**
     * getEditLinks
     *
     * @return string
     */
    protected function getEditLinks(): string
    {
        $isAdmin = sessionTools::isAdmin();
        $linkDetailId = ($this->hasValue(self::_ID)) ? '/id/' . $this->getParams(self::_ID) : '';
        $linkManage = ($isAdmin) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . self::LIST_ACTION,
            [self::_TITLE => 'Recherche de lignes']
        ) : '';
        $linkDetail = glyphHelper::getLinked(
            glyphHelper::EYE_OPEN,
            $this->baseUrl . self::DETAIL_ACTION . $linkDetailId,
            [self::_TITLE => 'Détail']
        );
        return $this->getWidgetLinkWrapper($linkManage . $linkDetail);
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
    protected function getIndexInputFilter(): inputFilter
    {
        return new inputFilter(
            $this->getParams(),
            [
            self::_ID => new inputRange([
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
    protected function tile($s, $x, $y, $z): string
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
    protected function downloadOsmMap($s, $x, $y, $z): string
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
