<?php
namespace App1\Model\Metro\Domain;

class Stations extends \Pimvc\Db\Model\Domain
{

    /**
     * @var integer id (comments)
     * @name id
     * @type Integer
     * @pdo 1
     * @length 11
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $id;

    /**
     * @var string lon (comments)
     * @name lon
     * @type String
     * @pdo 2
     * @length 12
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $lon;

    /**
     * @var string lat (comments)
     * @name lat
     * @type String
     * @pdo 2
     * @length 12
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $lat;

    /**
     * @var string name (comments)
     * @name name
     * @type String
     * @pdo 2
     * @length 150
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $name;

    /**
     * @var string h (comments)
     * @name h
     * @type String
     * @pdo 2
     * @length 16
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $h;
}
