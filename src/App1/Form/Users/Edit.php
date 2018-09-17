<?php
/**
 * Form_Users_Edit
 *
 * @author pierrefromager
 */
namespace App1\Form\Users;

use Pimvc\Form;
use Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Tools\Session as sessionTool;
use \App1\Model\Users as modelUser;
use Firebase\JWT\JWT as Fjwt;

class Edit extends Form
{
    const USER_EDIT_ACTION = 'user/edit';
    const USER_EDIT_FORM_NAME = 'user-edit';
    const USER_EDIT_FORM_METHOD = 'post';
    const USER_EDIT_DECORATOR_BREAK = '<br style="clear:both"/>';
    const PARAM_EMAIL = 'email';
    const PARAM_STATUS = 'status';
    const PARAM_SELECT = 'select';

    protected $isAdmin;
    protected $uid;
    protected $userModel;
    protected $postedData;
    protected $app;
    protected $baseUrl;
    protected $modelConfig;
    protected $jwtToken;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @param int $uid
     * @param string $mode
     * @return \Form_Users_Edit
     */
    public function __construct($postedDatas, $uid, $mode = '')
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->modelConfig = $this->app->getConfig()->getSettings('dbPool');
        $this->userModel = new modelUser($this->modelConfig);
        $this->uid = $uid;
        $this->isAdmin = sessionTool::isAdmin();
        $this->postedData = $postedDatas;
        $this->postedData['jwt_token'] = $this->getJwtToken(
            $this->postedData['id'],
            $this->postedData['login'],
            $this->postedData['password']
        );
        $this->setMode($mode);
        parent::__construct(
            $this->_getFields(),
            self::USER_EDIT_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::USER_EDIT_ACTION,
            self::USER_EDIT_FORM_METHOD,
            $this->postedData,
            $this->getUserForbidenFields()
        );
        $this->setType('fid', 'select');
        $this->setData('fid', $this->getReferents());
        $this->setExtra('fid', self::USER_EDIT_DECORATOR_BREAK);
        if ($this->isAdmin) {
            $this->setType('status', 'select');
            $this->setData('status', $this->userModel->getStatus());
            $this->setExtra('dateexp', self::USER_EDIT_DECORATOR_BREAK);
            $this->setExtra('name', self::USER_EDIT_DECORATOR_BREAK);
            $this->setType('profil', 'select');
            $roles = array_flip(\Pimvc\Tools\Format\Roles::getList());
            $this->setData('profil', $roles);
        }
        $this->setElementOptions('token', ['readonly' => 'readonly']);
        $this->_setWrappers();
        $this->setType('sexe', 'select');
        $this->setType('jwt_token', 'textarea');
        $this->setElementOptions(
            'jwt_token',
            ['readonly' => 'readonly', 'style' => 'min-height:100px;']
        );
        $sexeChoice = ['M' => 'Masculin', 'F' => 'Féminin', 'A' => 'Autre'];
        $this->setData('sexe', $sexeChoice);
        $this->setLabels($this->_getLabels());
        $this->setAlign('left');
        $this->Setsectionsize(20);
        $this->setExtra('name', $this->_getExtra());
        $this->setMode($mode);
        $postedXcsrf = (isset($postedDatas[self::FORM_XCSRF])) ? $postedDatas[self::FORM_XCSRF] : Form\Csrf::generate(
            self::FORM_XCSRF,
            $withOriginCheck = true
        );
        $this->setValue(self::FORM_XCSRF, $postedXcsrf);
        $validators = [
            self::PARAM_EMAIL => 'isemail'
            , self::FORM_XCSRF => 'validxcsrf'
        ];
        $this->setValidators($validators);
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Enregistrer');
        $this->render();
        unset($this->userModel);
        return $this;
    }

    /**
     * setJwtToken
     *
     */
    private function getJwtToken($id, $login, $password)
    {
        $tokenId = base64_encode(openssl_random_pseudo_bytes(32));
        $issuedAt = time();
        $notBefore = $issuedAt + 0;  //Adding 10 seconds
        $expire = $notBefore + (60 * 60 * 24); // Adding 60 seconds // 1 day
        $appInstance = \Pimvc\App::getInstance();
        $serverName = $appInstance->getRequest()->getHost();
        $data = [
            'iat' => $issuedAt, // Issued at: time when the token was generated
            'jti' => $tokenId, // Json Token Id: an unique identifier for the token
            'iss' => $serverName, // Issuer
            'nbf' => $notBefore, // Not before
            'exp' => $expire, // Expire
            'data' => [// Data related to the signer user
                'id' => $id, // userid from the users table
                'login' => $login, // User name
                'password_hash' => password_hash($password, PASSWORD_DEFAULT)
            ]
        ];
        $jwtConfig = $appInstance->getConfig()->getSettings('jwt');
        $secretKey = $jwtConfig['secret'];
        $algorithm = $jwtConfig['algorithm'];
        return Fjwt::encode($data, $secretKey, $algorithm);
    }

    /**
     * _setWrappers
     *
     */
    private function _setWrappers()
    {
        $elementWrapper = 'form-element-wrapper';
        $cols2 = $elementWrapper . ' col-sm-2';
        $cols4 = $elementWrapper . ' col-sm-4';
        $cols6 = $elementWrapper . ' col-sm-6';
        $cols12 = $elementWrapper . ' col-sm-12';
        $formControl = 'form form-control';
        $this->setWrapperClass('fid', $cols12);
        $this->setClass('fid', $formControl);

        $this->setWrapperClass('datec', $cols4);
        $this->setClass('datec', $formControl);
        $this->setWrapperClass('dateexp', $cols4);
        $this->setClass('dateexp', $formControl);
        $this->setWrapperClass('name', $cols4);
        $this->setClass('name', $formControl);
        $this->setWrapperClass('email', $cols4);
        $this->setClass('email', $formControl);

        $this->setWrapperClass('login', $cols4);
        $this->setClass('login', $formControl);
        $this->setWrapperClass('password', $cols4);
        $this->setClass('password', $formControl);
        $this->setWrapperClass('token', $cols4);
        $this->setClass('token', $formControl);

        $this->setWrapperClass('photo', $cols4);
        $this->setClass('photo', $formControl);
        $this->setWrapperClass('age', $cols4);
        $this->setClass('age', $formControl);
        $this->setWrapperClass('sexe', $cols4);
        $this->setClass('sexe', $formControl);

        $this->setWrapperClass('adresse', $cols6);
        $this->setClass('adresse', $formControl);
        $this->setWrapperClass('cp', $cols2);
        $this->setClass('cp', $formControl);
        $this->setWrapperClass('ville', $cols4);
        $this->setClass('ville', $formControl);

        $this->setWrapperClass('profil', $cols4);
        $this->setClass('profil', $formControl);
        $this->setWrapperClass('reference', $cols4);
        $this->setClass('reference', $formControl);
        $this->setWrapperClass('gsm', $cols4);
        $this->setClass('gsm', $formControl);

        $this->setWrapperClass('site', $cols12);
        $this->setClass('site', $formControl);

        $this->setWrapperClass('status', $cols6);
        $this->setClass('status', $formControl);

        $this->setWrapperClass('ip', $cols6);
        $this->setClass('ip', $formControl);

        $this->setClass('sn', $formControl);
        $this->setWrapperClass('sn', $cols12);

        $this->setWrapperClass('jwt_token', $cols12);
        $this->setClass('jwt_token', $formControl);
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        $fields = $this->userModel->getColumns();
        $fields[] = self::FORM_XCSRF;
        $fields[] = 'jwt_token';
        return $fields;
    }

    /**
     * _getLabels
     *
     * @return array
     */
    private function _getLabels()
    {
        return self::_getStaticLabels();
    }

    /**
     * _getStaticLabels
     *
     * @param boolean $withIcon
     * @return array
     */
    public static function _getStaticLabels($withIcon = true)
    {
        $labels = array(
            'fid' => 'Référent',
            'datec' => 'Date création',
            'dateexp' => 'Date expiration',
            'name' => 'Nom',
            'login' => 'Identifiant',
            'password' => 'Mot de passe',
            'token' => 'Jeton',
            'photo' => 'Photo',
            'age' => 'Age',
            'adresse' => 'Adresse',
            'cp' => 'Code postal',
            'ville' => 'Ville',
            self::PARAM_EMAIL => 'Email',
            'profil' => 'Rôle',
            'reference' => 'Ref. mailing',
            'gsm' => 'Téléphone',
            'site' => 'Site internet',
            self::PARAM_STATUS => 'Statut',
            'ip' => '@Ip',
            'sn' => 'Numéro série',
            'jwt_token' => 'Token Jwt'
        );
        if ($withIcon) {
            foreach ($labels as $key => $value) {
                $labels[$key] = self::_getLabelIcon($key) . $value;
            }
        }
        return $labels;
    }

    /**
     * _getLabelIcon
     *
     * @param string $fieldName
     * @return string
     */
    private static function _getLabelIcon($fieldName)
    {
        $icons = array(
            'fid' => glyphHelper::get(glyphHelper::TAG),
            'datec' => glyphHelper::get(glyphHelper::CALENDAR),
            'dateexp' => glyphHelper::get(glyphHelper::CALENDAR),
            'name' => glyphHelper::get(glyphHelper::USER),
            'login' => glyphHelper::get(glyphHelper::CERTIFICATE),
            'password' => glyphHelper::get(glyphHelper::LOCK),
            'token' => glyphHelper::get(glyphHelper::LOCK),
            'photo' => glyphHelper::get(glyphHelper::PICTURE),
            'age' => glyphHelper::get(glyphHelper::TIME),
            'adresse' => glyphHelper::get(glyphHelper::ROAD),
            'cp' => glyphHelper::get(glyphHelper::ROAD),
            'ville' => glyphHelper::get(glyphHelper::ROAD),
            self::PARAM_EMAIL => glyphHelper::get(glyphHelper::ENVELOPE),
            'profil' => glyphHelper::get(glyphHelper::CERTIFICATE),
            'reference' => glyphHelper::get(glyphHelper::ENVELOPE),
            'gsm' => glyphHelper::get(glyphHelper::EARPHONE),
            'site' => glyphHelper::get(glyphHelper::CLOUD),
            self::PARAM_STATUS => glyphHelper::get(glyphHelper::FLAG),
            'ip' => glyphHelper::get(glyphHelper::FIRE),
            'sn' => glyphHelper::get(glyphHelper::BARCODE),
            'jwt_token' => glyphHelper::get(glyphHelper::LOCK),
        );
        return isset($icons[$fieldName]) ? $icons[$fieldName] : '';
    }

    /**
     * _getExtra
     *
     * @return string
     */
    private function _getExtra()
    {
        return '';
        $uid = $this->postedData['id'];
        $email = (isset($this->postedData['email'])) ? $this->postedData['email'] : $this->getEmail($uid);
        $this->postedData['email'] = $email;
        return '<div style="width:200px;height:200px;float:right;margin-right:30px">'
            //. Helper_Format_Qrcode_Adressbook::get($this->postedData)
            . '</div>';
    }

    /**
     * getEmail
     *
     * @param int $uid
     * @return string
     */
    private function getEmail($uid)
    {
        $userModel = new Model_Users();
        $email = $userModel->getById($uid)->email;
        unset($userModel);
        return $email;
    }

    /**
     * getUserForbidenFields
     *
     * @return array
     */
    private function getUserForbidenFields()
    {
        return ($this->isAdmin) ? array() : array(
            'password'
            //, 'email'
            , 'login'
            , 'datec'
            , 'profil'
            , 'ip'
            , 'reference'
            , 'status'
            , 'sn'
        );
    }

    /**
     * getReferents
     *
     * @return array
     */
    private function getReferents()
    {
        $referents = [];
        $pros = $this->userModel->getPro();
        foreach ($pros as $referent) {
            $referents[$referent['id']] = $referent['name'];
        }
        unset($pros);
        return $referents;
    }

    /**
     * isValid
     *
     * @return type
     */
    public function isValid()
    {
        return parent::isValid();
    }
}
