<?php

namespace App1\Helper\Reuse;

trait Crud
{

    /**
     * setCrudInstance
     *
     */
    protected function setCrudInstance()
    {
        $this->fields = $this->getFields();
        $this->crudInstance = new \App1\Model\Crud(
            $this->slot,
            $this->adapter,
            $this->removeSchemaFromName($this->table),
            $this->modelConfig
        );
        $this->crudInstance->setDomainInstance(
            new \App1\Model\Domain\Crud($this->fields)
        );
        $this->crudInstance->setPrimary($this->getPkName());
    }

    /**
     * getFields
     *
     * @return \Pimvc\Db\Model\Fields
     */
    protected function getFields(): \Pimvc\Db\Model\Fields
    {
        $fields = new \Pimvc\Db\Model\Fields();
        $desc = [];
        if ($this->table && $this->slot) {
            $forge = new \Pimvc\Db\Model\Forge($this->slot);
            $descs = $forge->describeTable($this->table);
            $indexes = $forge->getIndexes($this->table);
            list($columnName, $columnPrimary, $columnPrimaryValue, $descName) = $this->fieldFactory();
            $indexeNames = array_map(function ($v) use ($columnName) {
                return $v[$columnName];
            }, $indexes);
            $pkFilter = array_filter($indexes, function ($v) use ($columnPrimary, $columnPrimaryValue) {
                return (isset($v[$columnPrimary]) && ($v[$columnPrimary] === $columnPrimaryValue));
            });
            $pkNames = array_map(function ($v) use ($columnName) {
                return $v[$columnName];
            }, $pkFilter);
            $is4d = ($this->adapter === \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D);
            foreach ($descs as $desc) {
                $f = new \Pimvc\Db\Model\Field();
                $isKey = in_array($desc[$descName], $indexeNames);
                $isPrimary = ($is4d) ? ($desc['primary'] == '1') : in_array($desc[$descName], $pkNames);
                $desc[\Pimvc\Db\Model\Field::_PRIMARY] = $isPrimary;
                $desc[\Pimvc\Db\Model\Field::_KEY] = $isKey;
                $f->setFromDescribe($this->adapter, $desc);
                $isLob = ($is4d && \Pimvc\Tools\Db\Fourd\Types::isFourdLob($desc['data_type']));
                if (!$isLob) {
                    $fields->addItem($f);
                } else {
                    //throw new \Exception('Cant use Lob type');
                }
                unset($f);
            }
        }
        return $fields;
    }

    /**
     * removeSchemaFromName
     *
     * @param string $tablename
     * @return string
     */
    protected function removeSchemaFromName(string $tablename): string
    {
        $parts = explode('.', $tablename);
        return (count($parts) > 1) ? $parts[1] : $tablename;
    }

    /**
     * getPkName
     *
     * @return string
     * @throws \Exception
     */
    protected function getPkName(): string
    {
        $pkColumns = array_filter(
            iterator_to_array($this->fields),
            function (\Pimvc\Db\Model\Field $v) {
                    return $v->getIsPrimaryKey();
            }
        );
        $pkColumn = array_shift($pkColumns);
        $pkColumnName = '';
        if ($pkColumn instanceof \Pimvc\Db\Model\Field) {
            $pkColumnName = $pkColumn->getName();
        } else {
            throw new \Exception('Cant find primary key');
        }
        return $pkColumnName;
    }

    /**
     * fieldFactory
     *
     * @return array
     */
    private function fieldFactory(): array
    {
        $columnName = '';
        $columnPrimary = '';
        $columnPrimaryValue = '';
        $descName = '';
        switch ($this->adapter) {
            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL:
                $columnName = 'column_name';
                $columnPrimary = 'key_name';
                $columnPrimaryValue = 'PRIMARY';
                $descName = 'field';
                break;

            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_SQLITE:
                $columnName = 'name';
                $columnPrimary = 'primary';
                $columnPrimaryValue = true;
                $descName = $columnName;
                break;

            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL:
                $columnName = 'attname';
                $columnPrimary = 'indisprimary';
                $columnPrimaryValue = true;
                $descName = 'column_name';
                break;

            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D:
                $columnName = 'column_name';
                $columnPrimary = 'primary';
                $columnPrimaryValue = '1';
                $descName = $columnName;
                break;
        }
        return [$columnName, $columnPrimary, $columnPrimaryValue, $descName];
    }

    /**
     * getUnprepSqlUpdateQuery
     *
     * @param mixed $pkValue
     * @param string $fieldName
     * @param string $fieldValue
     * @return string
     */
    protected function getUnprepSqlUpdateQuery($pkValue, string $fieldName, string $fieldValue, bool $quote = true): string
    {
        $value = ($quote) ? $this->getQuoted($fieldValue) : $fieldValue;
        return \Pimvc\Db\Model\Orm::MODEL_UPDATE . $this->crudInstance->getName()
                . \Pimvc\Db\Model\Orm::MODEL_SET
                . $fieldName . \Pimvc\Db\Model\Orm::MODEL_EQUAL
                . $value
                . \Pimvc\Db\Model\Orm::MODEL_WHERE
                . $this->getPkName()
                . \Pimvc\Db\Model\Orm::MODEL_EQUAL
                . $pkValue;
    }

    /**
     * getUnprepSqlSelectQuery
     *
     * @param mixed $pkValue
     * @param string $fieldName
     * @return string
     */
    protected function getUnprepSqlSelectQuery($pkValue, string $fieldName): string
    {
        return \Pimvc\Db\Model\Orm::MODEL_SELECT
                . $fieldName
                . \Pimvc\Db\Model\Orm::MODEL_FROM . $this->crudInstance->getName()
                . \Pimvc\Db\Model\Orm::MODEL_WHERE
                . $this->getPkName()
                . \Pimvc\Db\Model\Orm::MODEL_EQUAL
                . $pkValue;
    }

    /**
     * getTimeFromMs
     *
     * @param int $ms
     * @return string
     */
    protected function getTimeFromMs(int $ms): string
    {
        return gmdate('H:i:s', $ms / 1000);
    }

    /**
     * getQuoted
     *
     * @param string $v
     * @return string
     */
    protected function getQuoted(string $v): string
    {
        return "'" . $v . "'";
    }
}
