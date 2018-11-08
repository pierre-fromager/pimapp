<?php
/**
 * Description of App1\Tools\Date\Business
 *
 * @author pierrefromager
 */
namespace App1\Tools\Date;

class Business
{
    const FIRT_DAY_OF_MONTH = 'first day of this month';
    const LAST_DAY_OF_MONTH = 'last day of this month';
    const TODAY = 'Y-m-d';
    const YEAR = 'Y';

    private $startDate;
    private $endDate;
    private $nbDaysOff = 0;
    private $nbHolidays = 0;
    private $holidays = null;
    private $busdays = null;

    /**
     * __construct
     *
     */
    public function __construct()
    {
        if (!function_exists('easter_date')) {
            /*
              G is the Golden Number-1
              H is 23-Epact (modulo 30)
              I is the number of days from 21 March to the Paschal full moon
              J is the weekday for the Paschal full moon (0=Sunday,
              1=Monday, etc.)
              L is the number of days from 21 March to the Sunday on or before
              the Paschal full moon (a number between -6 and 28)
             */

            function easter_date($year)
            {
                $G = $year % 19;
                $C = (int) ($year / 100);
                $H = (int) ($C - (int) ($C / 4) - (int) ((8 * $C + 13) / 25) + 19 * $G + 15) % 30;
                $I = (int) $H - (int) ($H / 28) * (1 - (int) ($H / 28) * (int) (29 / ($H + 1)) * (int) ((21 - $G) / 11));
                $J = ($year + (int) ($year / 4) + $I + 2 - $C + (int) ($C / 4)) % 7;
                $L = $I - $J;
                $m = 3 + (int) (($L + 40) / 44);
                $d = $L + 28 - 31 * ((int) ($m / 4));
                $y = $year;
                $E = mktime(0, 0, 0, $m, $d, $y);
                return $E;
            }
        }
    }

    /**
     * setStartDate
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate = '')
    {
        $this->startDate = ($startDate) ? $startDate : $this->getToday();
        return $this;
    }

    /**
     * setEndDate
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate = '')
    {
        $this->endDate = ($endDate) ? $endDate : $this->getToday();
        return $this;
    }

    /**
     * setNbDaysOff
     *
     * @param int $nbdays
     * @return $this
     */
    public function setNbDaysOff($nbdays = 0)
    {
        $this->nbDaysOff = $nbdays;
        return $this;
    }

    /**
     * setHolidays
     *
     * @return $this
     */
    public function setHolidays()
    {
        $this->holidays = [];
        $dtStart = new \DateTime($this->startDate);
        $dtEnd = new \DateTime($this->endDate);
        $sYear = (int) date('Y', $dtStart->getTimestamp());
        $eYear = (int) date('Y', $dtEnd->getTimestamp());
        $holBase = [
            '01-01', '05-01', '05-08', '06-05'
            , '07-14', '08-15', '11-01', '11-11', '12-25'
        ];
        $counter = 0;
        for ($ny = $sYear; $ny <= $eYear; ++$ny) {
            $hol = array_merge($holBase, $this->getEasterDate($ny));
            $holSize = count($hol);
            for ($c = 0; $c < $holSize; ++$c) {
                $hoy = $ny . '-' . $hol[$c];
                $dtHo = new \DateTime($hoy);
                $tsHo = $dtHo->getTimestamp();
                $isGt = $tsHo >= $dtStart->getTimestamp();
                $isLt = $dtEnd->getTimestamp() >= $tsHo;
                if ($isGt && $isLt) {
                    $dayNum = date('N', $tsHo);
                    $isBizzDayChecking = (!in_array($dayNum, [6, 7]));
                    if ($isBizzDayChecking) {
                        ++$counter;
                        $this->holidays[] = $hoy;
                    }
                }
                unset($dtHo);
            }
        }
        unset($dtEnd);
        unset($dtStart);
        $this->nbHolidays = $counter;
        return $this;
    }

    public function getHolidayList()
    {
        return $this->holidays;
    }

    /**
     * getEasterDate
     *
     * @param int $year
     * @return array
     */
    private function getEasterDate($year)
    {
        $easterSunday = date("m-d", easter_date($year));
        $easterMonday = new \DateTime($year . '-' . $easterSunday . ' + 1 day');
        $busDay = $easterMonday->format('m-d');
        unset($easterMonday);
        unset($easterSunday);
        return [$busDay];
    }

    /**
     * getHolidays
     *
     * @return int
     */
    public function getHolidays()
    {
        return $this->nbHolidays;
    }

    /**
     * getToday
     *
     * @return string
     */
    public function getToday()
    {
        return date(self::TODAY);
    }

    /**
     * getFirstDayOfMonth
     *
     * @param string $date
     * @return string
     */
    public function getFirstDayOfMonth($date)
    {
        return $this->getModifiedDayOfMonth($date, self::FIRT_DAY_OF_MONTH);
    }

    /**
     * getLastDayOfMonth
     *
     * @param string $date
     * @return string
     */
    public function getLastDayOfMonth($date)
    {
        return $this->getModifiedDayOfMonth($date, self::LAST_DAY_OF_MONTH);
    }

    /**
     * getWorkingDays
     *
     * @return int
     */
    public function getWorkingDays()
    {
        $begin = strtotime($this->startDate);
        $end = strtotime($this->endDate);
        if ($begin > $end) {
            return 0;
        } else {
            $no_days = 0;
            $begin -= (86400);
            while ($begin <= $end) {
                if (!in_array(date('N', $begin), [6, 7])) {
                    $no_days++;
                }
                $begin += (86400);
            }
            return $no_days - ($this->nbDaysOff + $this->nbHolidays);
        }
    }

    /**
     * getModifiedDayOfMonth
     *
     * @param string $date
     * @param string $modifier
     * @return string
     */
    private function getModifiedDayOfMonth($date, $modifier)
    {
        $modifiedDayOfMonth = (new \DateTime($date))
            ->modify($modifier)
            ->format('Y-m-d');
        return $modifiedDayOfMonth;
    }
}
