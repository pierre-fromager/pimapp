<?php
namespace App1\Tools\Date;

use \Pimvc\Html\Element\Decorator;
use App1\Tools\Date\Business as businessTool;

class Calendar
{
    const CAL_PREV_LABEL = 'Prev';
    const CAL_NEXT_LABEL = 'Next';
    const CAL_DAY_LABELS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    const CAL_MAX_MONTH = 12;
    const PARAM_Y = 'Y';
    const PARAM_M = 'm';
    const PARAM_N = 'N';
    const MINUS = '-';
    const P_START = ' start ';
    const P_END = ' end ';
    const P_MASK = ' mask ';
    const P_BD = ' bd ';
    const P_01 = '01';
    const PARAM_DATA_DURATION = 'data-duration';
    const PARAM_CLASS = 'class';
    const PARAM_MONTH = 'month';
    const PARAM_YEAR = 'year';
    const PARAM_HREF = 'href';

    private $previousLabel;
    private $nextLabel;
    private $dayLabels = [];
    private $currentYear = 0;
    private $currentMonth = 0;
    private $currentDay = 0;
    private $currentDate = null;
    private $daysInMonth = 0;
    private $naviHref = null;
    private $bi = null;
    private $holidays = [];

    /**
     * __construct
     *
     * @param string $baseUrl
     */
    public function __construct($baseUrl = '')
    {
        $this->setBaseUrl($baseUrl);
        $this->setLabels();
        $this->setDayLabels();
        return $this;
    }

    /**
     * setBaseUrl
     *
     * @param string $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->naviHref = $baseUrl;
        return $this;
    }

    /**
     * setYear
     *
     * @param int $year
     * @return $this
     */
    public function setYear($year)
    {
        $this->currentYear = $year;
        return $this;
    }

    /**
     * setMonth
     *
     * @param int $month
     * @return $this
     */
    public function setMonth($month)
    {
        $this->currentMonth = $month;
        return $this;
    }

    /**
     * setLabels
     *
     * @param string $previous
     * @param string $next
     * @return $this
     */
    public function setLabels($previous = '', $next = '')
    {
        $this->previousLabel = ($previous) ? $previous : self::CAL_PREV_LABEL;
        $this->nextLabel = ($next) ? $next : self::CAL_NEXT_LABEL;
        return $this;
    }

    /**
     * setDayLabels
     *
     * @param array $dayLabels
     * @return $this
     */
    public function setDayLabels($dayLabels = [])
    {
        $this->dayLabels = ($dayLabels) ? $dayLabels : self::CAL_DAY_LABELS;
        return $this;
    }

    /**
     * show
     *
     * @return string
     */
    public function show()
    {
        $this->bi = $this->getBusinessInstance();
        $this->holidays = $this->bi->getHolidayList();
        $this->daysInMonth = $this->daysInMonth($this->currentMonth, $this->currentYear);
        $content = '<div id="calendar">' .
            '<div class="box">' .
            $this->createNavi() .
            '</div>' .
            '<div class="box-content">' .
            '<ul class="label">' . $this->createLabels() . '</ul>';
        $content .= '<div class="clear">' .
            '</div>' .
            '<ul class="dates">';
        $weeksInMonth = $this->weeksInMonth($this->currentMonth, $this->currentYear);
        $clear = new Decorator('div', '', [self::PARAM_CLASS => 'clear']);
        for ($i = 0; $i < $weeksInMonth; $i++) {
            for ($j = 1; $j <= 7; $j++) {
                $content .= $this->showDay($i * 7 + $j);
            }
            $content .= $clear;
        }
        $content .= '</ul>' . $clear . '</div></div>';
        return $content;
    }

    /**
     * showDay
     *
     * @param type $cellNumber
     * @return type
     */
    private function showDay($cellNumber)
    {
        if ($this->currentDay == 0) {
            $firstDayOfTheWeekTs = $this->getShowFirstDayCurrentWeek();
            $firstDayOfTheWeek = date(self::PARAM_N, $firstDayOfTheWeekTs);
            if (intval($cellNumber) == intval($firstDayOfTheWeek)) {
                $this->currentDay = 1;
            }
        }
        if (($this->currentDay != 0) && ($this->currentDay <= $this->daysInMonth)) {
            $this->currentDate = $this->getShowDaysCurrentDate();
            $cellContent = $this->currentDay;
            $this->currentDay++;
        } else {
            $this->currentDate = null;
            $cellContent = null;
        }
        $id = ($this->currentDate) ? ' id="' . $this->currentDate . '" ' : '';
        $dd7 = $cellNumber % 7;
        $class = '';
        $busDay = !($dd7 === 0 || $dd7 === 6);
        $sup = '';
        $dataDuration = '';
        $dataStatus = '';
        if ($busDay) {
            if (in_array($this->currentDate, $this->holidays)) {
                $busDay = false;
            }
        }
        if ($dd7 === 1) {
            $class .= self::P_START;
        }
        if ($dd7 === 0) {
            $class .= self::P_END;
        }
        if (is_null($cellContent)) {
            $class .= self::P_MASK;
        } elseif ($busDay) {
            $class .= self::P_BD;
            $defaultDuration = 0;
            $sup = new Decorator('sub', $defaultDuration);
            $dataDuration = self::PARAM_DATA_DURATION . '="' . $defaultDuration . '"';
            $defaultStatus = 1;
            $dataStatus = ' data-status' . '="' . $defaultStatus . '"';
        } else {
            $class .= ' do ';
        }

        return '<li ' . $id . ' class="' . $class . '"  ' . $dataDuration . $dataStatus . '>'
            . $cellContent . $sup . '</li>';
    }

    /**
     * getShowFirstDayCurrentWeek
     *
     * @return date
     */
    private function getShowFirstDayCurrentWeek()
    {
        return strtotime($this->currentYear . self::MINUS . $this->currentMonth . self::MINUS . self::P_01);
    }

    /**
     * getShowDaysCurrentDate
     *
     * @return date
     */
    private function getShowDaysCurrentDate()
    {
        $timeString = $this->currentYear . self::MINUS . $this->currentMonth
            . self::MINUS . $this->currentDay;
        return date('Y-m-d', strtotime($timeString));
    }

    /**
     * create navigation
     */
    private function createNavi()
    {
        $nextMonth = ($this->currentMonth == self::CAL_MAX_MONTH) ? 1 : intval($this->currentMonth) + 1;
        $nextYear = ($this->currentMonth == self::CAL_MAX_MONTH) ? intval($this->currentYear) + 1 : $this->currentYear;
        $preMonth = ($this->currentMonth == 1) ? self::CAL_MAX_MONTH : intval($this->currentMonth) - 1;
        $preYear = ($this->currentMonth == 1) ? intval($this->currentYear) - 1 : $this->currentYear;
        return '<div class="header">' .
            $this->getNavLink(
                $this->getNavUrls($preMonth, $preYear),
                'Get previous month',
                'prev',
                $this->getNavArrow('fa fa-arrow-left') . $this->previousLabel
            ) .
            $this->getNaviHeaderTitle() .
            '<span id="dayscount"></span>' .
            $this->getNavLink(
                $this->getNavUrls($nextMonth, $nextYear),
                'Get next month',
                'next',
                $this->nextLabel . '&nbsp;' . $this->getNavArrow('fa fa-arrow-right')
            ) .
            '</div>';
    }

    /**
     * getNaviHeaderTitle
     *
     * @return Decorator
     */
    private function getNaviHeaderTitle()
    {
        $naviHeaderTitleContent = date(
            'Y M',
            strtotime(
                $this->currentYear . self::MINUS
                . $this->currentMonth . self::MINUS . '1'
            )
        );
        return new Decorator(
            'span',
            $naviHeaderTitleContent,
            [self::PARAM_CLASS => 'title']
        );
    }

    /**
     * getNavArrow
     *
     * @param string $arrowClass
     * @return Decorator
     */
    private function getNavArrow($arrowClass)
    {
        return new Decorator(
            'span',
            '&nbsp;',
            [self::PARAM_CLASS => $arrowClass]
        );
    }

    /**
     * getNavLink
     *
     * @param string $url
     * @param string $tooltip
     * @param string $class
     * @param string $content
     * @return Decorator
     */
    private function getNavLink($url, $tooltip, $class, $content)
    {
        return new Decorator(
            'a',
            $content,
            [
            self::PARAM_CLASS => $class,
            self::PARAM_HREF => $url,
            'data-placement' => 'top',
            'data-toggle' => 'tooltip',
            'title' => $tooltip
            ]
        );
    }

    /**
     * getNavUrls
     *
     * @param int $month
     * @param int $year
     * @return string
     */
    private function getNavUrls($month, $year)
    {
        return $this->naviHref . DIRECTORY_SEPARATOR .
            self::PARAM_MONTH . DIRECTORY_SEPARATOR .
            sprintf('%02d', $month) . DIRECTORY_SEPARATOR .
            self::PARAM_YEAR . DIRECTORY_SEPARATOR . $year;
    }

    /**
     * create calendar week labels
     */
    private function createLabels()
    {
        $content = '';
        foreach ($this->dayLabels as $index => $label) {
            $content .= '<li class="' .
                ($label == 6 ? 'end title' : 'start title') . ' title">' .
                $label .
                '</li>';
        }
        return $content;
    }

    /**
     * calculate number of weeks in a particular month
     */
    private function weeksInMonth($month = null, $year = null)
    {
        if (null == ($year)) {
            $year = date(self::PARAM_Y, time());
        }
        if (null == ($month)) {
            $month = date(self::PARAM_M, time());
        }
        // find number of days in this month
        $daysInMonths = $this->daysInMonth($month, $year);
        $numOfweeks = ($daysInMonths % 7 == 0 ? 0 : 1) + intval($daysInMonths / 7);
        $monthEndingDay = date(self::PARAM_N, strtotime($year . self::MINUS . $month . self::MINUS . $daysInMonths));
        $monthStartDay = date(self::PARAM_N, strtotime($year . self::MINUS . $month . self::MINUS . self::P_01));
        if ($monthEndingDay < $monthStartDay) {
            $numOfweeks++;
        }
        return $numOfweeks;
    }

    /**
     * calculate number of days in a particular month
     */
    private function daysInMonth($month = null, $year = null)
    {
        if (null == ($year)) {
            $year = date(self::PARAM_Y, time());
        }
        if (null == ($month)) {
            $month = date(self::PARAM_M, time());
        }
        return date('t', strtotime($year . self::MINUS . $month . self::MINUS . self::P_01));
    }

    /**
     * getBusinessInstance
     *
     * @return businessTool
     */
    private function getBusinessInstance()
    {
        $business = new businessTool();
        $runningDate = $this->currentYear . self::MINUS . $this->currentMonth .
            self::MINUS . self::P_01;
        $dateStart = $business->getFirstDayOfMonth($runningDate);
        $dateEnd = $business->getLastDayOfMonth($runningDate);
        $business->setStartDate($dateStart)
            ->setEndDate($dateEnd)
            ->setNbDaysOff(0)
            ->setHolidays();
        return $business;
    }
}
