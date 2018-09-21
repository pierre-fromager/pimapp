<?php

/**
 * App1\Helper\Format\Metro\Lignes\Colors
 *
 */

namespace App1\Helper\Format\Metro\Lignes;

class Colors
{

    private static $ligneColors = [
        'A' => '#cf532e',
        'A1' => '#cf532e',
        'A2' => '#cf532e',
        'A3' => '#cf532e',
        'A35' => '#cf532e',
        'A4' => '#cf532e',
        'A5' => '#cf532e',
        'B' => '#5191cd',
        'B2' => '#5191cd',
        'B3' => '#5191cd',
        'B4' => '#5191cd',
        'B5' => '#5191cd',
        'C' => '#fcd946',
        'C1' => '#fcd946',
        'C2' => '#fcd946',
        'C4' => '#fcd946',
        'C46' => '#fcd946',
        'C468' => '#fcd946',
        'C5' => '#fcd946',
        'C57' => '#fcd946',
        'C6' => '#fcd946',
        'C7' => '#fcd946',
        'C8' => '#fcd946',
        'D1' => '#00aa69',
        'D2' => '#00aa69',
        'D4' => '#00aa69',
        'D6' => '#00aa69',
        'E1' => '#d892bd',
        'E2' => '#d892bd',
        'E4' => '#d892bd',
        'H' => '#95564d',
        'H1' => '#95564d',
        'H11' => '#95564d',
        'H12' => '#95564d',
        'H2' => '#95564d',
        'H21' => '#95564d',
        'H211' => '#95564d',
        'H22' => '#95564d',
        'H3' => '#95564d',
        'M1' => '#fdce00',
        'M10' => '#dfb039',
        'M11' => '#8e6538',
        'M12' => '#328e5b',
        'M13' => '#a0cccb',
        'M14' => '#612684',
        'M2' => '#0267af',
        'M3' => '#a1971b',
        'M3 b' => '#96d7dd',
        'M4' => '#b74288',
        'M5' => '#de8b53',
        'M6' => '#79bb92',
        'M7' => '#e8a8b8',
        'M7 b' => '#7cc485',
        'M8' => '#cfa9d0',
        'M9' => '#cec92b',
        'P' => '#f0ad19',
        'T1' => '#0564b1',
        'T2' => '#c74896',
        'T3' => '#90613b',
        'T4' => '#fcc11b',
        'T5' => '#0564b1',
        'T6' => '#ec5738',
        'T7' => '#90613b',
        'T8' => '#999738',
    ];

    /**
     * get
     *
     * @param string $ligne
     * @return type
     */
    public static function get($ligne = '')
    {
        return isset(self::$ligneColors[$ligne]) ? self::$ligneColors[$ligne] : '#FF0000';
    }
}
