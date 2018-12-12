<?php

/**
 * Description of App1\Form\Files\Upload
 *
 * @todo http://www.php.net/manual/fr/session.upload-progress.php
 *
 * @author pierrefromager
 */

namespace App1\Form\Files;

use Pimvc\Form;
use \Pimvc\Tools\Flash as flashTools;

class Upload extends Form
{

    const FORM_FILE_METHOD = 'post';
    const FORM_FILE_NAME = 'file-upload';
    const FORM_FILE_ACTION = 'file/upload';
    const FORM_FILE_MAX_FILESIZE = 2097152;
    const FORM_FILE_ENCTYPE = 'multipart/form-data';
    const FORM_FIELD_FILENAME = 'filename';
    const FORM_ERROR_MIME_INCOMPAT = 'Type mime incompatible';
    const FORM_ERROR_EXT_INCOMPAT = 'Format de fichier incompatible';
    const FORM_ERROR_NULL_FILESIZE = 'La taille du fichier est nulle.';
    const FORM_MAXSIZE_FIELD = 'MAX_FILE_SIZE';

    private $_postedData = array();
    private $_maxsize;
    private $_allowedTypes = array();
    private $_allowedExtensions = array();
    private $_destination = '';
    private $_filename = '';
    private $_tmp_name = '';
    private $_error = null;
    private $_errorUpload = 0;
    private $_size = 0;
    private $_type;
    private $_extension;

    /**
     * __construct
     *
     * @param array $data
     */
    public function __construct($postedData, $action = '', $maxsize = '')
    {
        $this->_postedData = array_merge($postedData, $_FILES);
        $this->_setMaxsize($maxsize);
        $fieldList = array(self::FORM_MAXSIZE_FIELD, self::FORM_FIELD_FILENAME);
        $fieldExlude = array();
        if ($this->isPost() && isset($this->_postedData[self::FORM_FIELD_FILENAME])) {
            $this->setInfos();
            $this->_errorUpload = $this->_postedData[self::FORM_FIELD_FILENAME]['error'];
            unset($this->_postedData[self::FORM_FIELD_FILENAME]);
            $this->setData(self::FORM_FIELD_FILENAME, $this->_filename);
        }
        parent::__construct(
            $fieldList,
            self::FORM_FILE_NAME,
            $action,
            self::FORM_FILE_METHOD,
            $this->_postedData,
            $fieldExlude
        );
        $this->setEncType(self::FORM_FILE_ENCTYPE);
        $this->setType(self::FORM_FIELD_FILENAME, 'file');
        $this->setLabel(self::FORM_FIELD_FILENAME, 'Fichier');
        $this->setType(self::FORM_MAXSIZE_FIELD, 'hidden');
        $this->setData(self::FORM_MAXSIZE_FIELD, $this->_maxsize);
        $this->setValidators($this->_getValidators());
        $this->setAction($action);
        $this->setAlign('normal');
        $this->render();
    }

    /**
     * getValidators
     *
     * @return array
     */
    private function _getValidators()
    {
        return [
            self::FORM_FIELD_FILENAME => 'isrequired'
            , self::FORM_MAXSIZE_FIELD => 'isnumeric'
        ];
    }

    /**
     * isValid
     *
     * @return type
     */
    public function isValid()
    {
        $isValid = true;
        $flashErrors = array();

        if ($this->_errorUpload != 0) {
            $isValid = false;
            $message = \App1\Tools\File\Upload::getErrorMessage($this->_errorUpload);
            $flashErrors[] = $message;
        }
        if ($this->_allowedTypes) {
            if (!in_array($this->_type, $this->_allowedTypes)) {
                $flashErrors[] = self::FORM_ERROR_MIME_INCOMPAT;
            }
        }
        if ($this->_allowedExtensions) {
            if (!in_array($this->_extension, $this->_allowedExtensions)) {
                $flashErrors[] = self::FORM_ERROR_EXT_INCOMPAT;
            }
        }
        foreach ($flashErrors as $flashError) {
            flashTools::addError($flashError);
        }
        return $isValid;
    }

    /**
     * _setDestination
     *
     * @param string $destination
     */
    public function _setDestination($destination)
    {
        $this->_destination = $destination;
        return $this;
    }

    /**
     * _getUploadInfos
     *
     * @return \stdClass
     */
    public function _getUploadInfos()
    {
        $uploadInfos = new \stdClass();
        $uploadInfos->destination = $this->_destination;
        $uploadInfos->filename = $this->_filename;
        $uploadInfos->tmpname = $this->_tmp_name;
        $uploadInfos->size = $this->_size;
        $uploadInfos->type = $this->_type;
        return $uploadInfos;
    }

    /**
     * _move
     *
     * @return boolean
     */
    public function _move()
    {
        $returnCode = false;
        if ($this->_moveable()) {
            $returnCode = move_uploaded_file(
                $this->_tmp_name,
                $this->_destination . $this->_filename
            );
        }
        return $returnCode;
    }

    /**
     * _setMaxsize
     *
     * @param int $maxsize
     */
    public function _setMaxsize($maxsize)
    {
        $this->_maxsize = (empty($maxsize)) ? self::FORM_FILE_MAX_FILESIZE : $maxsize;
        return $this;
    }

    /**
     * _getMaxsize
     *
     * @return int $maxsize
     */
    public function _getMaxsize()
    {
        return $this->_maxsize;
    }

    /**
     * _setAllowedType
     *
     * @param array $allowedTypes
     */
    public function _setAllowedType($allowedTypes)
    {
        $this->_allowedTypes = $allowedTypes;
        return $this;
    }

    /**
     * _setAllowedExtension
     *
     * @param array $allowedType
     */
    public function _setAllowedExtension($allowedExtensions)
    {
        $this->_allowedExtensions = $allowedExtensions;
        return $this;
    }

    /**
     * _hasDestination
     *
     * @return boolean
     */
    private function _hasDestination()
    {
        return !empty($this->_destination);
    }

    /**
     * _hasTmpname
     *
     * @return boolean
     */
    private function _hasTmpname()
    {
        return !empty($this->_tmp_name);
    }

    /**
     * _moveable
     *
     * @return boolean
     */
    private function _moveable()
    {
        return ($this->_hasDestination() && $this->_hasTmpname());
    }

    /**
     * setInfos
     *
     */
    private function setInfos()
    {
        $isUpload = isset($_FILES[self::FORM_FIELD_FILENAME]);
        if ($isUpload) {
            $fileInfo = $_FILES[self::FORM_FIELD_FILENAME];
            $this->_filename = $fileInfo['name'];
            $basename = basename($this->_filename);
            $this->_extension = (!empty($basename)) ? substr($basename, -4) : '';
            $this->_tmp_name = $fileInfo['tmp_name'];
            $this->_error = $fileInfo['error'];
            $this->_type = $fileInfo['type'];
            $this->_size = $fileInfo['size'];
            unset($fileInfo);
        }
        return $this;
    }

    /**
     * isPost
     *
     * @return boolean
     */
    private function isPost()
    {
        return (\Pimvc\App::getInstance()->getRequest()->getMethod() === 'POST');
    }
}
