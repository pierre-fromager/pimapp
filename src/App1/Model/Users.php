<?php

/**
 * Description of App1\Model\Users
 *
 * @author pierrefromager
 */

namespace App1\Model;

use \Pimvc\Tools\Session as sessionTools;

class Users extends \Pimvc\Model\Users
{

    const USERS_STATUS_VALID = 'valid';
    const USERS_STATUS_WAITING = 'waiting';
    const _SN = 'sn';

    protected $_slot = 'db1';
    protected $_name = 'user';
    protected $_primary = 'id';
    protected $_alias = 'users';
    protected $userInfoFields = array(
        self::_ID
        //, 'iid'
        , self::_NAME
        , self::_EMAIL
        , self::_PASSWORD
        , self::_PROFIL
        , self::_STATUS
        , self::_SN
    );

    /**
     * __construct
     *
     * @param array $config
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        return $this;
    }

    /**
     * getRolesDash
     *
     * @return array
     */
    public function getRolesDash()
    {
        return $this->find(
            [self::_PROFIL],
            [],
            [],
            [],
            self::_PROFIL
        )->getRowsetAsArray();
    }

    /**
     * getDashDaysRequest
     *
     * @param string $monthPattern
     * @return array
     */
    public function getDashDaysRequest($monthPattern = '')
    {
        $expr = 'SUBSTRING_INDEX( datec,  \' \', 1)';
        return $this->getDateFactory($expr, $monthPattern);
    }

    /**
     * getYearWeeksVolume
     *
     * @return array
     */
    public function getYearWeeksVolume()
    {
        $sql = self::MODEL_SELECT .
                " CONCAT(YEAR(datec), '/', WEEK(datec)) AS week_name," .
                " YEAR(datee), WEEK(datec), COUNT(*) AS counter " .
                " FROM " . $this->_name .
                self::MODEL_GROUP_BY . "week_name" .
                self::MODEL_ORDER .
                "YEAR(datec) ASC," .
                "WEEK(datec) ASC";
        $this->run($sql);
        $results = array();
        foreach ($this->_statement->fetchAll() as $row) {
            $results[] = ['datec' => $row['week_name'], 'counter' => $row['counter']];
        }
        return $results;
    }

    /**
     * getBoxSn
     *
     * @return string
     */
    public function getBoxSn()
    {
        $snResult = $this->find(
            [self::_SN],
            [$this->_primary => sessionTools::getUid()]
        )->getCurrent();
        return (isset($snResult->sn)) ? $snResult->sn : '';
    }

    /**
     * getDateFactory
     *
     * @param string $expr
     * @param string $monthPattern
     * @return array
     */
    private function getDateFactory($expr, $monthPattern = '', $alias = 'datec')
    {
        $this->cleanRowset();
        $where = array();
        if ($this->isUsDate($monthPattern)) {
            $where[$alias] = $monthPattern . '%';
        }
        $limit = [];
        $groupBy = $expr;
        $exprAlias = self::MODEL_ALIAS . $alias;
        $this->find(
            [$expr . $exprAlias],
            $where,
            [$alias => 'asc'],
            $limit,
            $groupBy
        );
        return $this->getRowsetAsArray();
    }

    /**
     * isUsDate
     *
     * @param string $date
     * @return boolean
     */
    private function isUsDate($date)
    {
        $shortDatePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])$/';
        $dateShortCheck = (boolean) (preg_match($shortDatePattern, $date));
        $longDatePattern = '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/';
        $dateLongCheck = (boolean) (preg_match($longDatePattern, $date));
        return ($dateShortCheck || $dateLongCheck);
    }
}
