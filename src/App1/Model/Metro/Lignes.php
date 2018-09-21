<?php

/**
 * Description App1\Model\Metro\Lignes
 *
 * @author pierrefromager
 */

namespace App1\Model\Metro;

use \Pimvc\Db\Model\Orm;
use \Pimvc\Helper\Math\Geo\Distance as geoDistance;

class Lignes extends Orm
{

    const _LIGNE = 'ligne';
    const _SRC = 'src';
    const _DST = 'dst';
    const _HSRC = 'hsrc';
    const _HDST = 'hdst';
    const _LAT = 'lat';
    const _LON = 'lon';
    const _DIST = 'dist';
    const _INFINITE = 66666;

    protected $_name = 'metro_lig_node';
    protected $_primary = 'id';
    protected $_alias = 'metrolignode';
    protected $_adapter = parent::MODEL_ADAPTER_DEFAULT;
    protected $_refMap = [
        \App1\Model\Metro\Stations::class => [
            self::_LOCAL => self::_HSRC,
            self::_FOREIGN => \App1\Model\Metro\Stations::_H,
            self::_ALIAS => 'metrostageo',
            self::_TABLE => 'metro_sta_geo',
        ],
    ];
    private $_weights = [];
    private $_nodes = [];

    /**
     * __construct
     *
     * @param type $config
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * getById
     *
     * @param int $id
     * @param array $what
     * @return \App1\Model\Metro\Domain\Lignes
     */
    public function getById($id, $what = [])
    {
        $this->cleanRowset();
        $this->find($what, [$this->_primary => $id]);
        $r = $this->getRowsetAsArray();
        return (count($r) > 0) ? $r[0] : [];
    }

    /**
     * getNames
     *
     * @return array
     */
    public function getLignesValues()
    {
        $this->cleanRowset();

        $this->find(
            [self::_LIGNE],
            [],
            $sort = [self::_LIGNE => Orm::MODEL_ORDER_ASC],
            $limit = [],
            $groupBy = self::_LIGNE
        );

        return $this->getRowsetAsArray();
    }

    /**
     * rebuildh
     */
    private function rebuildh()
    {
        $this->run($this->md5Update(self::_HSRC, self::_SRC));
        $this->run($this->md5Update(self::_HDST, self::_DST));
    }

    /**
     * md5update
     *
     * @param string $target
     * @param string $targetValue
     * @return string
     */
    private function md5Update($target, $targetValue)
    {
        return $this->_sqlModelUpdate() . $target . '= MD5(' . $targetValue . ')';
    }

    /**
     * _sqlModelUpdate
     *
     * @return string
     */
    private function _sqlModelUpdate()
    {
        return parent::MODEL_UPDATE . $this->_name . parent::MODEL_SET;
    }

    /**
     * kmDistances
     *
     * @param array $stations
     */
    public function kmDistances($stations = [])
    {
        $lc = count($stations);
        $matrix = [];
        for ($col = 0; $col < $lc; $col++) {
            $hsrc = $stations[$col];
            for ($row = 0; $row < $lc; $row++) {
                $hdst = $stations[$row];
                $matrix[$hsrc][$hdst] = $this->weight($hsrc, $hdst);
            }
        }
        return $matrix;
    }

    /**
     * adjacency
     *
     * @return array
     */
    public function adjacence()
    {
        return $this->nodes();
    }

    /**
     * adjacency
     *
     * @return array
     */
    public function weightedNodes()
    {
        $nodes = $this->nodes();
        $keys = array_keys($nodes);
        $nc = count($nodes);
        for ($c = 0; $c < $nc; $c++) {
            $k = $keys[$c];
            $v = $nodes[$k];
            $lv = count($v);
            $nodes[$k] = [];
            for ($vc = 0; $vc < $lv; $vc++) {
                $nodes[$k][$v[$vc]] = $this->weight($k, $v[$vc]);
            }
        }
        return $nodes;
    }

    /**
     * weight
     *
     * @param string $hsrc
     * @param string $hdst
     * @return float
     */
    private function weight($hsrc, $hdst)
    {
        if ($hsrc == $hdst) {
            return 0;
        }
        if (!$this->_weights) {
            $this->cleanRowset();
            $this->find(
                [$this->_primary, self::_HSRC, self::_HDST, self::_DIST]
            );
            $lines = $this->getRowsetAsArray();
            $lc = count($lines);
            for ($c = 0; $c < $lc; $c++) {
                $_hsrc = $lines[$c][self::_HSRC];
                $_hdst = $lines[$c][self::_HDST];
                $weight = $lines[$c][self::_DIST];
                $this->_weights[$_hsrc][$_hdst] = $weight;
            }
        }
        $weighted = isset($this->_weights[$hsrc][$hdst]);
        return ($weighted) ? $this->_weights[$hsrc][$hdst] : self::_INFINITE;
    }

    /**
     * nodes
     *
     * @param string $hsrc
     * @param boolean $weighted
     * @return array
     */
    private function nodes($hsrc = '')
    {
        if (!$this->_nodes) {
            $this->cleanRowset();
            $this->find(
                [self::_HSRC, self::_HDST]
            );
            $lines = $this->getRowsetAsArray();
            $lc = count($lines);
            for ($c = 0; $c < $lc; $c++) {
                $_hsrc = $lines[$c][self::_HSRC];
                $_hdst = $lines[$c][self::_HDST];
                $this->_nodes[$_hsrc][] = $_hdst;
            }
        }
        if (!$hsrc) {
            return $this->_nodes;
        }
        $nodeExists = isset($this->_nodes[$hsrc]);
        return $nodeExists ? $this->_nodes[$hsrc] : [];
    }

    /**
     * updateDistances
     *
     * @param array $hList
     */
    public function updateDistances($hList = [])
    {
        $this->rebuildh();
        if ($hList) {
            $what = [$this->_primary, self::_HSRC, self::_HDST];
            $lignes = $this->find($what)->getRowsetAsArray();
            $lignesCount = count($lignes);
            for ($c = 0; $c < $lignesCount; $c++) {
                $ligne = $lignes[$c];
                $hsrc = $ligne[self::_HSRC];
                $hdst = $ligne[self::_HDST];
                if (isset($hList[$hsrc]) && isset($hList[$hdst])) {
                    $s = $hList[$hsrc];
                    $d = $hList[$hdst];
                    $dist = geoDistance::twoPoints(
                        $s[self::_LAT],
                        $s[self::_LON],
                        $d[self::_LAT],
                        $d[self::_LON]
                    );
                    $this->updateDistByH($ligne[$this->_primary], $dist);
                }
            }
        }
    }

    /**
     * getSrcStation
     *
     * @param string $hsrc
     * @return \App1\Model\Metro\Domain\Station
     */
    public function getSrcStation($hsrc)
    {
        return $this->getDependantObjects(self::_HSRC, $hsrc);
    }

    /**
     * getDstStation
     *
     * @param string $hdst
     * @return \App1\Model\Metro\Domain\Station
     */
    public function getDstStation($hdst)
    {
        return $this->getDependantObjects(self::_HDST, $hdst);
    }

    /**
     * getTroncon
     *
     * @param string $hsrc
     * @param string $hdst
     * @return \App1\Model\Metro\Domain\Ligne
     */
    public function getTroncon($hsrc, $hdst)
    {
        $this->cleanRowset();
        $this->find([], [self::_HSRC => $hsrc, self::_HDST => $hdst]);
        $all = $this->getRowsetAsArray();
        if ($all) {
            $first = $all[0];
            $first['geo'] = $this->getGeoTroncon($hsrc, $hdst);
            return $first;
        }
        return [];
    }

    /**
     * getGeoTroncon
     *
     * @param string $hsrc
     * @param string $hdst
     * @return [[],[]]
     */
    public function getGeoTroncon($hsrc, $hdst)
    {
        $srcgeo = $this->getDependantObjects(self::_HSRC, $hsrc)->metrostageo;
        $dstgeo = $this->getDependantObjects(self::_HSRC, $hdst)->metrostageo;
        return [[$srcgeo->lat, $srcgeo->lon], [$dstgeo->lat, $dstgeo->lon]];
    }

    /**
     * updateDistByH
     *
     * @param int $id
     * @param double $dist
     */
    private function updateDistByH($id, $dist)
    {
        $this->cleanRowset();
        $this->find([], [$this->_primary => $id]);
        $res = $this->getCurrent();
        if ($res) {
            $res->dist = $dist;
            $this->save($res);
        }
    }
}
