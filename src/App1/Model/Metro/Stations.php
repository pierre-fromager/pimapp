<?php
/**
 * App1\Model\Metro\Stations
 *
 */
namespace App1\Model\Metro;

use App1\Helper\Math\Geo\Distance as geoDistance;

class Stations extends \Pimvc\Db\Model\Orm
{
    const _H = 'h';
    const _NAME = 'name';
    const _LAT = 'lat';
    const _LON = 'lon';
    const _DISTANCE = 'distance';

    protected $_name = 'metro_sta_geo';
    protected $_primary = 'id';
    protected $_alias = 'metrostageo';
    protected $_adapter = parent::MODEL_ADAPTER_DEFAULT;
    protected $_refMap = [];
    protected $hList = [];

    /**
     * __construct
     *
     * @param array $config
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
     * @return \App1\Model\Metro\Domain\Stations
     */
    public function getById($id, $what = [])
    {
        $this->cleanRowset();
        $this->find($what, [$this->_primary => $id]);
        $r = $this->getRowsetAsArray();
        return (count($r) > 0) ? $r[0] : [];
    }

    /**
     * getByH
     *
     * @param int $id
     * @param array $what
     * @return \Model_Domain_Users
     */
    public function getByH($h = '')
    {
        $this->cleanRowset();
        if (!$this->hList) {
            $this->setHlist();
        }
        if ($h && isset($this->hList[$h])) {
            return $this->hList[$h];
        }
        return $this->hList;
    }

    /**
     * rebuildh
     */
    public function rebuildh()
    {
        $this->run($this->md5Update(self::_H, self::_NAME));
    }

    /**
     * getSortedDistanceFrom
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function getSortedDistancesFrom($lat, $lon)
    {
        $this->cleanRowset();
        $coords = $this->find([])->getRowsetAsArray();
        $farthests = [];
        $count = count($coords);
        for ($c = 0; $c < $count - 1; $c++) {
            $coord = $coords[$c];
            $coord[self::_DISTANCE] = geoDistance::twoPoints(
                $lat,
                $lon,
                $coord[self::_LAT],
                $coord[self::_LON]
            );
            $farthests[] = $coord;
        }
        usort($farthests, function ($a, $b) {
            return $a[self::_DISTANCE] > $b[self::_DISTANCE];
        });
        unset($coords);
        return $farthests;
    }

    /**
     * getFarthest
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function getFarthest($lat, $lon)
    {
        $distances = $this->getSortedDistancesFrom($lat, $lon);
        return array_pop($distances);
    }

    /**
     * getClosest
     *
     * @param float $lat
     * @param float $lon
     * @return array
     */
    public function getClosest($lat, $lon)
    {
        return $this->getSortedDistancesFrom($lat, $lon)[0];
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
     * setHlist
     *
     */
    private function setHlist()
    {
        $this->cleanRowset();
        $this->find([$this->_primary, self::_H, self::_NAME, self::_LON, self::_LAT]);
        $rows = $this->getRowsetAsArray();
        $this->hList = [];
        foreach ($rows as $value) {
            $h = $value[self::_H];
            $this->hList[$h] = $value;
        }
    }
}
