<?php

namespace App1\Model\Domain;

class Crud extends \Pimvc\Db\Model\Domain
{

    private $data;
    private $fields;

    public function __construct(\Pimvc\Db\Model\Fields $fields)
    {
        $this->fields = $fields;
        parent::__construct();
    }

    /**
     * __get
     *
     * @param string $varName
     * @return mixed
     * @throws Exception
     */
    public function __get($varName)
    {
        if (!array_key_exists($varName, $this->data)) {
            //this attribute is not defined!
            throw new Exception('.....');
        } else {
            return $this->data[$varName];
        }
    }

    /**
     * __set
     *
     * @param string $varName
     * @param mixed $value
     */
    public function __set($varName, $value)
    {
        $this->data[$varName] = $value;
    }

    /**
     * __isset
     *
     * @param type $varName
     * @return boolean
     */
    public function __isset($varName)
    {
        return isset($this->data[$varName]);
    }

    /**
     * hydrate assigns values
     *
     * @param array $array
     */
    public function hydrate($array)
    {
        $classKeys = $this->getMembers();
        foreach ($classKeys as $property) {
            $value = (isset($array[$property])) ? $array[$property] : self::FORBIDENKEYS;
            if (in_array($property, $classKeys) && ($value !== self::FORBIDENKEYS)
            ) {
                $this->$property = $value;
            } else {
                unset($this->$property);
            }
        }
    }

    /**
     * get
     *
     * @return \stdClass
     */
    public function get()
    {
        $proxy = new \stdClass;
        $members = $this->getMembers();
        foreach ($members as $member) {
            $proxy->{$member} = $this->data[$member];
        }
        return $proxy;
    }

    /**
     * getMembers
     *
     * @return array
     */
    private function getMembers()
    {
        return array_map(function (\Pimvc\Db\Model\Field $field) {
            return $field->getName();
        }, iterator_to_array($this->fields));
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->data;
    }
}
