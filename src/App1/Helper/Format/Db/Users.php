<?php
/**
 * App1\Helper\Format\Db\Users
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 */
namespace App1\Helper\Format\Db;

use Pimvc\Helper\Format\Db as dbHelper;
use \Pimvc\Helper\Format\Interfaces\Liste as listeInterface;

class Users extends dbHelper implements listeInterface
{
    private static $instance;
    protected $domainName = \App1\Model\Domain\Users::class;
    protected $modelName = \App1\Model\Users::class;
    protected $keySearch = self::PARAM_ID;
    protected $keyValue = 'name';

    /**
     * getInstance
     *
     * @return Pimvc\Helper\Format\Db\Users
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * getStatic
     *
     * @param mixed $value
     * @return mixed
     */
    public static function getStatic($value)
    {
        return self::getInstance()->get($value);
    }

    /**
     * @see  __construct
     *
     */
    public function __construct()
    {
        parent::__construct();
    }
}
