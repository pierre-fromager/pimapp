<?php
/**
 *  LangController set the current locale
 */
namespace App1\Controller;

class Lang extends \Pimvc\Controller\Basic
{
    const ERROR_READING = "Une erreur s'est produite lors de la lecture, merci de vérifier le fichier (entetes, données)";
    const CSV_DONE = "Les données ont bien été enregistrées";
    const IMPORT_PARTIAL_NAME = 'lang_import.html';
    const UPLOAD_MAX_FILESIZE = 2097152;
    const EXPORT_NO_DATA_FOUND = 'Aucune donnée trouvée pour la langue demandée';
    const LANG_SET_ACTION = 'lang/set';
    const LANG_IMPORT_ACTION = 'lang/import';
    const LANG_EXPORT_ACTION = 'lang/export';
    const LANG_REFERER = 'HTTP_REFERER';

    private $langs = [];
    private $locale;
    private $translator;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->getApp()->setLocale('ru-RU');

        $this->locale = $this->getApp()->getLocale();

        $this->getApp()->setTranslator();
        $this->translator = $this->getApp()->getTranslator();
        $this->langs = $this->getApp()->getConfig()->getSettings('app')['langs'];
        $this->request = $this->getApp()->getRequest()->get();
    }

    /**
     * set
     *
     * @return array
     */
    public function set()
    {
        var_dump($this->translator->translate('login'));
        die;
        $referer = $this->getApp()->getRequest()->getBaseUrl();
        if ($wanted = $this->getParams('name')) {
            foreach ($this->langs as $langCode => $langName) {
                if ($langCode == $wanted) {
                    $locale = $langCode . '-' . strtoupper($langCode);
                    $this->getApp()->setLocale($locale);
                }
            }
        }
        return $this->redirect($referer);
    }
    /*     * *
     * import
     *
     */

    public function import()
    {
        $msg = null;
        $lang = $this->getParams('lang');
        if ($this->request->isPost()) {
            $msg = self::CSV_DONE;
            if (!is_uploaded_file($_FILES["filename"]["tmp_name"])) {
                $msg = Tools_File_Upload::getErrorMessage($_FILES['filename']['error']);
            }
            if (!Tools_Lang::import($lang, $_FILES["filename"]["tmp_name"])) {
                $msg = self::ERROR_READING;
            }
        }
        $langOptions = array();
        foreach ($this->langs as $lang) {
            $langOptions[$lang->label] = $lang->name;
        }
        $langSelector = new Helper_Select(
            'lang',
            'lang',
            '',
            $langOptions
        );
        $helperOption = array(
            'langSelector' => $langSelector,
            'baseurl' => Tools_Session::getBaseUrl(),
            'import_action' => self::LANG_IMPORT_ACTION,
            'max_file_size' => self::UPLOAD_MAX_FILESIZE
        );
        $uploadHelper = new Helper_Partial($helperOption, self::IMPORT_PARTIAL_NAME);
        $content = $msg ? '<h3>' . $msg . '</h3>' : '';
        $content .= $uploadHelper;
        return array('content' => $content, 'menu' => array());
    }

    /**
     * export
     *
     * @return array
     */
    public function export()
    {
        $msg = '';
        $lang = $this->getParams('lang');
        if ($lang) {
            $res = Tools_Lang::export($lang);
            if (!$res) {
                $msg = self::EXPORT_NO_DATA_FOUND;
            }
        }
        $baseurl = Tools_Session::getBaseUrl();
        $url = $baseurl . self::LANG_EXPORT_ACTION;
        $content = $msg ? '<h3>' . $msg . '</h3>' : '';
        $langList = array();
        foreach (Tools_Array::ota($this->langs) as $langDefinition) {
            $langList[$langDefinition['label']] = $langDefinition['name'];
        }
        $langSelector = Tools_Urlselector::get(
            'langExportSelector',
            $url . '/lang/',
            $langList,
            $lang
        );
        $content .= $langSelector;
        return array('content' => $content);
    }
}
