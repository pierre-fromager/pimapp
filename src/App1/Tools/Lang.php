<?php

/**
 * Description of App1\Tools\Lang
 *
 * @author Pierre Fromager
 */

namespace App1\Tools;

use Pimvc\File\Csv\Parser as csvParser;

class Lang
{

    const _LANGSRC = 'langsrc';
    const _LANGDST = 'langdst';
    const _COMMENT = 'comment';
    const _ECSV = '.csv';
    const LANG_PATH_SUFFIX = '../public/lang/';
    const ERROR_MALFORMED = 500;

    private static $lang;
    private static $csvInstance;
    private static $error;

    /**
     * import
     *
     * @param string $lang
     * @param string $filenameOrData
     * @return boolean
     */
    public static function import($lang, $filenameOrData)
    {
        self::$error = 0;
        self::$lang = str_replace('/', '', filter_var($lang, FILTER_SANITIZE_STRING));
        if (!self::$lang || mb_strlen(self::$lang) != 2) {
            return false;
        }
        $unlinkafter = false;
        if (is_array($filenameOrData)) {
            $temp_file = tempnam(sys_get_temp_dir(), 'csv');
            $fp = fopen($temp_file, 'w');
            $headers = array_keys($filenameOrData[0]);
            fputcsv($fp, $headers);
            foreach ($filenameOrData as $fields) {
                fputcsv($fp, $fields);
            }
            fclose($fp);
            $filenameOrData = $temp_file;
            $unlinkafter = true;
        }

        self::$csvInstance = new csvParser();
        if (!self::isValidImport($filenameOrData)) {
            self::$error = self::ERROR_MALFORMED;
            return false;
        }
        $data = self::$csvInstance->unparse(
            self::$csvInstance->data,
            [],
            null,
            null,
            ','
        );

        if ($unlinkafter) {
            unlink($filenameOrData);
        }
        $writeResult = @file_put_contents(self::getPath(), $data);
        if ($writeResult == false) {
            self::$error = UPLOAD_ERR_CANT_WRITE;
        }
        return $writeResult;
    }

    /**
     * export
     *
     * @param string $lang
     * @return boolean
     */
    public static function export($lang)
    {
        self::$error = 0;
        self::$lang = $lang;
        self::$csvInstance = new csvParser();
        if (self::$csvInstance->auto(self::getPath()) == false) {
            self::$error = self::ERROR_MALFORMED;
            return false;
        }
        self::$csvInstance->output(self::$lang . self::_ECSV);
        exit();
    }

    /**
     * getData
     *
     * @param string $lang
     * @return boolean
     */
    public static function getData($lang)
    {
        self::$error = 0;
        self::$lang = $lang;
        self::$csvInstance = new csvParser();
        if (self::$csvInstance->auto(self::getPath()) == false) {
            self::$error = self::ERROR_MALFORMED;
            return false;
        }
        return self::$csvInstance->data;
    }

    /**
     * getPath
     *
     * @return string
     */
    public static function getPath()
    {
        $appPath = \Pimvc\App::getInstance()->getPath();
        $langPath = $appPath . self::LANG_PATH_SUFFIX;
        $path = $langPath . self::$lang . self::_ECSV;
        return $path;
    }

    /**
     * getError
     *
     * @return int
     */
    public static function getError()
    {
        return self::$error;
    }

    /**
     * isValidImport
     *
     * @param string $filenameOrData
     * @return boolean
     */
    private static function isValidImport($filenameOrData)
    {
        $auto = self::$csvInstance->auto($filenameOrData);
        if ($auto !== false) {
            $hasData = (count(self::$csvInstance->data) > 0);
            $hasColumn = count(self::$csvInstance->data[0]) == 3;
            $hasScrCol = isset(self::$csvInstance->data[0][self::_LANGSRC]);
            $hasDstCol = isset(self::$csvInstance->data[0][self::_LANGSRC]);
            return ($hasData && $hasColumn && $hasScrCol && $hasDstCol);
        }
        self::$error = self::ERROR_MALFORMED;
        return $auto;
    }
}
