<?php

/**
 * Description of App1\Tools\File\Upload
 *
 * @author pierrefromager
 */

namespace App1\Tools\File;

class Upload implements IUpload
{

    /**
     * getErrorMessage
     *
     * return error message for a given upload code error.
     *
     * @param int $errorCode
     * @return string
     */
    public static function getErrorMessage($errorCode)
    {
        $uploadErrorMessage = self::UPLOAD_ERR_UNKOWN;
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                $uploadErrorMessage = self::UPLOAD_ERR_INI_SIZE
                        . ini_get('upload_max_filesize') . '.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $uploadErrorMessage = self::UPLOAD_ERR_FORM_SIZE . ini_get('upload_max_filesize') . '.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $uploadErrorMessage = self::UPLOAD_ERR_NO_TMP_DIR;
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $uploadErrorMessage = self::UPLOAD_ERR_CANT_WRITE;
                break;
            case UPLOAD_ERR_EXTENSION:
                $uploadErrorMessage = self::UPLOAD_ERR_EXTENSION;
                break;
            case UPLOAD_ERR_PARTIAL:
                $uploadErrorMessage = self::UPLOAD_ERR_PARTIAL;
                break;
            case UPLOAD_ERR_NO_FILE:
                $uploadErrorMessage = self::UPLOAD_ERR_NO_FILE;
                break;
            default:
                $uploadErrorMessage = 'Unknown Error';
                break;
        }
        return $uploadErrorMessage;
    }
}
