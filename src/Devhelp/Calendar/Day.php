<?php

namespace Devhelp\Calendar;

/**
 * Stores calendar day info.
 * 
 * @package devhelp/calendar
 * @author Paweł Barański <pawel.baranski1@gmail.com>
 */
class Day
{
    /** @var integer day number (1-[29-31]), depending on month)*/
    protected $day;
    /** @var integer month number (1-12)*/
    protected $month;

    /**
     * Converts DateTime object to Day object
     * @param \DateTime $dt
     * @return Day
     */
    public static function fromDateTime(\DateTime $dt)
    {
        return new static($dt->format('j'), $dt->format('n'));
    }

    public function __construct($day, $month)
    {
        $this->checkMonth($month);
        $this->checkDay($day, $month);

        $this->month = $month;
        $this->day = $day;
    }

    /**
     * Returns month
     * @return integer
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Returns day
     * @return integer
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * Checks if month is valid. Throws exception if not
     * @param integer $month
     * @throws \InvalidArgumentException
     */
    protected function checkMonth($month)
    {
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException(
                "Month has invalid value: $month, should be [1-12]",
                500
            );
        }
    }

    /**
     * Checks if day is valid. Throws exception if not 
     * @param integer $day
     * @param integer $month
     * @throws \InvalidArgumentException
     */
    protected function checkDay($day, $month)
    {
        $dayInMonth = array(1 => 31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        if ($day < 1 || $day > 31) {
            throw new \InvalidArgumentException(
                "Day has invalid value: $day, ".
                "should be [1-31], depending on month",
                501
            );
        }
        if ($day > $dayInMonth[$month]) {

            $monthName = date('F', mktime(0, 0, 0, $month));

            throw new \InvalidArgumentException(
                "Day has invalid value: $day, ".
                "should be [1-$dayInMonth[$month]] in $monthName",
                502
            );
        }
    }

    /**
     * Converts Day object to DateTime object
     * @param integer $year
     * @return \DateTime
     */
    public function toDateTime($year)
    {
        $dt = new \DateTime();
        $dt->setDate($year, $this->getMonth(), $this->getDay());
        $dt->setTime(0, 0, 0);

        return $dt;
    }

    /**
     * Checks if day is equal to another day
     * @param Day $day
     * @return boolean
     */
    public function equals(Day $day)
    {
        return $this->getDay() == $day->getDay() && $this->getMonth() == $day->getMonth();
    }

    /**
     * Checks if day is earlier than another day
     * @param Day $day
     * @return boolean
     */
    public function isEarlierThan(Day $day)
    {
        if ($this->getMonth() < $day->getMonth()) {
            return true;
        } elseif ($this->getMonth() == $day->getMonth()) {
            if ($this->getDay() < $day->getDay()) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Returns next Day (calculated for given year)
     * @param integer $year
     * @return Day
     */
    public function next($year)
    {
        $dt = $this->toDateTime($year);
        $dt->modify('+1 day');

        return Day::fromDateTime($dt);
    }
}
