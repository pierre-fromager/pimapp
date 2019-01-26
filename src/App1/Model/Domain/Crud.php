<?php

namespace App1\Model\Domain;

class Crud extends \Pimvc\Db\Model\Domain
{

    private $data;
    private $fields;

    /**
     * __construct
     *
     * @param \Pimvc\Db\Model\Fields $fields
     * @return $this
     */
    public function __construct(\Pimvc\Db\Model\Fields $fields)
    {
        $this->fields = $fields;
        parent::__construct();
        return $this;
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
    public function __isset($varName): bool
    {
        return isset($this->data[$varName]);
    }

    /**
     * hydrate
     *
     * @param mixed $array
     * @param bool $utfConvert
     * @return $this
     */
    public function hydrate($array, $utfConvert = false)
    {
        $classKeys = $this->getMembers();
        if ($utfConvert) {
            $this->charsetConvert($array);
        }
        foreach ($classKeys as $property) {
            $value = (isset($array[$property])) ? $array[$property] : self::FORBIDENKEYS;
            if (in_array($property, $classKeys) && ($value !== self::FORBIDENKEYS)
            ) {
                $this->$property = $value;
            } else {
                unset($this->$property);
            }
        }
        return $this;
    }

    /**
     * getMetas
     *
     * @return \Pimvc\Db\Model\Field
     */
    public function getMetas(string $propName): \Pimvc\Db\Model\Field
    {
        $ffield = array_filter(
            iterator_to_array($this->fields),
            function (\Pimvc\Db\Model\Field $field) use ($propName) {
                    return $propName == $field->getName();
            }
        );
        return array_values($ffield)[0];
    }

    /**
     * getPdos
     *
     * @return array
     */
    public function getPdos(): array
    {
        $fields = array_map(
            function (\Pimvc\Db\Model\Field $field) {
                    return [$field->getName() => $field->getPdoType()];
            },
            iterator_to_array($this->fields)
        );
        $count = count($fields);
        $types = [];
        for ($c = 0; $c < $count; ++$c) {
            $field = $fields[$c];
            $k = key($field);
            $types[$k] = $field[$k];
        }
        unset($fields, $count);
        return $types;
    }

    /**
     * getNativTypes
     *
     * @return array
     */
    public function getNativTypes(): array
    {
        $fields = array_map(
            function (\Pimvc\Db\Model\Field $field) {
                    return [$field->getName() => $field->getNativType()];
            },
            iterator_to_array($this->fields)
        );
        $count = count($fields);
        $types = [];
        for ($c = 0; $c < $count; ++$c) {
            $field = $fields[$c];
            $k = key($field);
            $types[$k] = $field[$k];
        }
        unset($fields, $count);
        return $types;
    }

    /**
     * getBooleansFields
     *
     * @return array
     */
    public function getBooleansFields(): array
    {
        $fields = $this->getPdos();
        $booleansFields = [];
        foreach ($fields as $key => $value) {
            if ($value === \PDO::PARAM_BOOL) {
                $booleansFields[] = $key;
            }
        }
        unset($fields);
        return $booleansFields;
    }

    /**
     * getFourdDatesFields
     *
     * @return array
     */
    public function getFourdDatesFields(): array
    {
        $fields = $this->getNativTypes();
        $timeFields = [];
        foreach ($fields as $key => $value) {
            if ($value === 8) {
                $timeFields[] = $key;
            }
        }
        unset($fields);
        return $timeFields;
    }

    /**
     * getRealFields
     *
     * @return array
     */
    public function getFourdRealFields(): array
    {
        $fields = $this->getNativTypes();
        $timeFields = [];
        foreach ($fields as $key => $value) {
            if ($value === 6) {
                $timeFields[] = $key;
            }
        }
        unset($fields);
        return $timeFields;
    }

    /**
     * getTimeFields
     *
     * @return array
     */
    public function getFourdTimeFields(): array
    {
        $fields = $this->getNativTypes();
        $timeFields = [];
        foreach ($fields as $key => $value) {
            if ($value === 9) {
                $timeFields[] = $key;
            }
        }
        unset($fields);
        return $timeFields;
    }

    /**
     * get
     *
     * @return \stdClass
     */
    public function get(): \stdClass
    {
        $proxy = new \stdClass;
        $members = $this->getMembers();
        foreach ($members as $member) {
            if (isset($this->data[$member])) {
                $proxy->{$member} = $this->data[$member];
            }
        }
        return $proxy;
    }

    /**
     * getMembers
     *
     * @return array
     */
    private function getMembers(): array
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
    public function toArray(): array
    {
        return (array) $this->data;
    }
}
