<?php
namespace App1\Model\Metro\Domain;

class Lignes extends \Pimvc\Db\Model\Domain
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
     * @var string ligne (comments)
     * @name ligne
     * @type String
     * @pdo 2
     * @length 4
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $ligne;

    /**
     * @var string src (comments)
     * @name src
     * @type String
     * @pdo 2
     * @length 150
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $src;

    /**
     * @var string hsrc (comments)
     * @name hsrc
     * @type String
     * @pdo 2
     * @length 16
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $hsrc;

    /**
     * @var string dst (comments)
     * @name dst
     * @type String
     * @pdo 2
     * @length 150
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $dst;

    /**
     * @var string hdst (comments)
     * @name hdst
     * @type String
     * @pdo 2
     * @length 16
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $hdst;

    /**
     * @var string dist (comments)
     * @name dist
     * @type String
     * @pdo 2
     * @length 12
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $dist;
}
