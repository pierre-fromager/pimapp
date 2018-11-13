<?php
/**
 *  App1\Controller\Lang
 */
namespace App1\Controller;

use App1\Tools\File\Upload as toolsFileUpload;
use App1\Tools\Lang as langTools;
use App1\Helper\Controller\Lang as langHelperController;
use App1\Views\Helpers\Select as selectHelper;
use Pimvc\Views\Helpers\Fa as faHelper;
use Pimvc\Tools\Flash as flashTools;
use \App1\Helper\Lang\IEntries as ILang;

class Lang extends langHelperController
{

    const _LANG = 'lang';
    const _TMP_NAME = 'tmp_name';
    const _VIEW_IMPORT = '/Views/Lang/Import.php';

    /**
     * default redirect to change
     *
     * @return array
     */
    final public function index()
    {
        return $this->redirect($this->baseUrl . '/lang/change');
    }

    /**
     * change
     *
     * @return array
     */
    final public function change()
    {
        if ($wanted = $this->getParams(self::_NAME)) {
            foreach ($this->langs as $langCode => $langName) {
                if ($langCode == $wanted) {
                    $locale = $langCode . '-' . strtoupper($langCode);
                    $this->getApp()->setLocale($locale);
                }
            }
            return $this->redirect($this->baseUrl);
        }
        $widget = $this->getWidget(
            faHelper::get(faHelper::LANGUAGE) . 'Choose langue',
            $this->getChangeLinks() . '<br style="clear:both"/>'
        );
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * import
     *
     * @return string
     */
    final public function import()
    {
        $msg = null;
        $lang = $this->getParams(self::_LANG);
        if ($this->isPost()) {
            $msg = self::CSV_DONE;
            if (!is_uploaded_file($_FILES[self::_FILENAME][self::_TMP_NAME])) {
                $errorCode = $_FILES[self::_FILENAME]['error'];
                flashTools::addError(toolsFileUpload::getErrorMessage($errorCode));
            }
            if (!langTools::import(
                $lang,
                $_FILES[self::_FILENAME][self::_TMP_NAME]
            )
            ) {
                $errorCode = langTools::getError();
                flashTools::addError(toolsFileUpload::getErrorMessage($errorCode));
            }
        }
        $langOptions = [];
        foreach ($this->langs as $lang) {
            $label = $lang[self::_LABEL];
            $name = $lang[self::_NAME];
            $langOptions[$label] = $name;
        }
        $langSelector = new selectHelper(
            self::_LANG,
            self::_LANG,
            '',
            $langOptions
        );
        $viewPath = $this->getApp()->getPath() . self::_VIEW_IMPORT;
        $langView = (new \Pimvc\View())
            ->setFilename($viewPath)
            ->setParams([
                'langSelector' => $langSelector,
                'baseurl' => $this->baseUrl,
                'import_action' => self::_IMPORT_ACTION,
                'max_file_size' => self::UPLOAD_MAX_FILESIZE
            ])
            ->render();
        $widget = $this->getWidget(
            faHelper::get(faHelper::LANGUAGE) . 'Import langue',
            (string) $langView
        );
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * export
     *
     * @return array
     */
    final public function export()
    {
        $lang = $this->getParams(self::_LANG);
        if ($lang) {
            if (!langTools::export($lang)) {
                flashTools::addError(self::EXPORT_NO_DATA_FOUND);
            }
        }
        $url = $this->baseUrl . self::_EXPORT_ACTION;
        $langList = [];
        $langCollection = \Pimvc\Tools\Arrayproto::ota($this->langs);
        foreach ($langCollection as $langDefinition) {
            $langList[$langDefinition[self::_LABEL]] = $langDefinition[self::_NAME];
        }
        $widgetContent = \Pimvc\Views\Helpers\Urlselector::get(
            'langExportSelector',
            $url . '/lang/',
            $langList,
            $lang
        ) . '<br style="clear:both"/>';
        $widget = $this->getWidget(
            faHelper::get(faHelper::LANGUAGE) . 'Export langue',
            $widgetContent
        );
        return (string) $this->getLayout((string) $widget);
    }
}
