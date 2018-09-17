<?php
/**
 * Description of model\domain\users
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 */
namespace App1\Model\Domain;

class Users extends \Pimvc\Model\Domain\Users
{

    /**
     * @var integer id (comments)
     * @name id
     * @type Integer
     * @pdo 1
     * @length 20
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $id;

    /**
     * @var integer fid (comments)
     * @name fid
     * @type Integer
     * @pdo 1
     * @length 20
     * @index 1
     * @pk 0
     * @ft null
     * @fk null
     */
    public $fid;

    /**
     * @var string datec (comments)
     * @name datec
     * @type String
     * @pdo 2
     * @length 12
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $datec;

    /**
     * @var string dateexp (comments)
     * @name dateexp
     * @type String
     * @pdo 2
     * @length 50
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $dateexp;

    /**
     * @var string name (comments)
     * @name name
     * @type String
     * @pdo 2
     * @length 50
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $name;

    /**
     * @var string email (comments)
     * @name email
     * @type String
     * @pdo 2
     * @length 100
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $email;

    /**
     * @var string login (comments)
     * @name login
     * @type String
     * @pdo 2
     * @length 50
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $login;

    /**
     * @var string password (comments)
     * @name password
     * @type String
     * @pdo 2
     * @length 30
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $password;

    /**
     * @var string token (comments)
     * @name token
     * @type String
     * @pdo 2
     * @length 32
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $token;

    /**
     * @var string photo (comments)
     * @name photo
     * @type String
     * @pdo 2
     * @length 200
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $photo;

    /**
     * @var string age (comments)
     * @name age
     * @type String
     * @pdo 2
     * @length 3
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $age;

    /**
     * @var string sexe (comments)
     * @name sexe
     * @type String
     * @pdo 2
     * @length 10
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $sexe;

    /**
     * @var string adresse (comments)
     * @name adresse
     * @type String
     * @pdo 2
     * @length 400
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $adresse;

    /**
     * @var string cp (comments)
     * @name cp
     * @type String
     * @pdo 2
     * @length 5
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $cp;

    /**
     * @var string ville (comments)
     * @name ville
     * @type String
     * @pdo 2
     * @length 40
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $ville;

    /**
     * @var string profil (comments)
     * @name profil
     * @type String
     * @pdo 2
     * @length 20
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $profil;

    /**
     * @var integer reference (comments)
     * @name reference
     * @type Integer
     * @pdo 1
     * @length 4
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $reference;

    /**
     * @var string gsm (comments)
     * @name gsm
     * @type String
     * @pdo 2
     * @length 30
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $gsm;

    /**
     * @var string site (comments)
     * @name site
     * @type String
     * @pdo 2
     * @length 400
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $site;

    /**
     * @var string status (comments)
     * @name status
     * @type String
     * @pdo 2
     * @length 15
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $status;

    /**
     * @var string ip (comments)
     * @name ip
     * @type String
     * @pdo 2
     * @length 16
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $ip;

    /**
     * @var string sn (comments)
     * @name sn
     * @type String
     * @pdo 2
     * @length 20
     * @index 0
     * @pk 0
     * @ft null
     * @fk null
     */
    public $sn;
}
