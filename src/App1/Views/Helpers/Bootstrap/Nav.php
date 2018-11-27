<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Nav
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

class Nav extends \Pimvc\View
{

    const _TEMPLATE = __DIR__ . '/Template/Nav.php';
    const _MARKIN = '{{';
    const _MARKOUT = '}}';
    const _MARKINNER = '(.*?)';
    const _SL = '/';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';

    /**
     * __construct
     *
     * @return $this
     */
    public function __construct()
    {
        $this->setFilename(self::_TEMPLATE);
        parent::__construct();
        return $this;
    }

    /**
     * translate
     *
     * @return $this
     */
    public function translate()
    {
        $translator = \Pimvc\App::getInstance()->getTranslator();
        if ($translator instanceof \Pimvc\Tools\Translator) {
            $this->parse($translator);
        }
        return $this;
    }

    /**
     * transMark
     *
     * @param string $key
     * @return string
     */
    public static function transMark(string $key): string
    {
        return self::_MARKIN . $key . self::_MARKOUT;
    }

    /**
     * menuAction
     *
     * @param string $title
     * @param string $icon
     * @param string $action
     * @param string $baseUrl
     * @return array
     */
    public static function menuAction(string $title, string $icon, string $action, string $baseUrl = ''): array
    {
        $url = ($baseUrl) ? $baseUrl : \Pimvc\App::getInstance()->getRequest()->getBaseUrl();
        return [
            self::_TITLE => $title
            , self::_ICON => $icon
            , self::_LINK => $url . $action
        ];
    }

    /**
     * parse
     *
     * @param string $content
     * @param array $tokens
     * @param \Pimvc\Tools\Translator $translator
     */
    private function parse(\Pimvc\Tools\Translator $translator)
    {
        $content = $this->getContent();
        if ($content && $tokens = $this->getTokens($content)) {
            $parsedTokens = [];
            $translateKeys = array_values(array_unique($tokens[0]));
            $count = count($translateKeys);
            for ($c = 0; $c < $count; ++$c) {
                $parsedTokens[] = $translator->translate(
                    $this->clearMarker($translateKeys[$c])
                );
            }
            $this->setContent(str_replace($translateKeys, $parsedTokens, $content));
            unset($translateKeys, $parsedTokens);
        }
        unset($content);
    }

    /**
     * getTokens
     *
     * @param string $content
     * @return array
     */
    private function getTokens(string $content): array
    {
        $hasToken = preg_match_all($this->tokenPattern(), $content, $matches);
        return $hasToken ? $matches : [];
    }

    /**
     * removeMarker
     *
     * @param string $key
     * @return string
     */
    private function clearMarker(string $key): string
    {
        return str_replace([self::_MARKIN, self::_MARKOUT], [''], $key);
    }

    /**
     * tokenPattern
     *
     * @return string
     */
    private function tokenPattern(): string
    {
        return self::_SL . self::_MARKIN . self::_MARKINNER . self::_MARKOUT . self::_SL;
    }
}
