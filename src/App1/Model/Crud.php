<?php
/**
 * Description of App1\Model\Crud
 *
 * @author pierrefromager
 */
namespace App1\Model;

class Crud extends \Pimvc\Db\Model\Orm
{

    protected $_slot = '';
    protected $_name = '';
    protected $_primary = '';
    protected $_alias = '';
    protected $_adapter = '';
    protected $_refMap = [];
    protected $_schema = '';

    /**
     * __construct
     *
     * @param type $config
     */
    public function __construct($slot, $adapter, $name, $config = [])
    {
        $this->setSlot($slot)->setAdapter($adapter)->setName($name);
        parent::__construct($config);
        $this->_schema = '';
    }

    /**
     * getById
     *
     * @param int $id
     * @param array $what
     * @return App1\Model\Crud
     */
    public function getById($id, $what = [])
    {
        $this->cleanRowset();
        $this->find($what, [$this->_primary => $id]);
        $r = $this->getRowsetAsArray();
        return (count($r) > 0) ? $r[0] : [];
    }

    /**
     * setAdapter
     *
     * @param type $adapter
     */
    protected function setAdapter($adapter)
    {
        $this->_adapter = $adapter;
        return $this;
    }

    /**
     * setName
     *
     * @param type $name
     */
    protected function setName($name)
    {
        $this->_name = $name;
        return $this;
    }

    /**
     * setSlot
     * @param type $slot
     */
    protected function setSlot($slot)
    {
        $this->_slot = $slot;
        return $this;
    }
}
