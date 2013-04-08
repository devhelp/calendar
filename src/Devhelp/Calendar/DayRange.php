<?php

namespace Devhelp\Calendar;

/**
 * Purpose of the class is to define and calculate day range
 * between two calendar days. Order of days is irrelevant,
 * days can be defined by Day, DayReference or Closure
 * 
 * @package devhelp/calendar
 * @author Paweł Barański <pawel.baranski1@gmail.com>
 */
class DayRange
{

    /** @var Day|DayReference|Closure day definition or plain day object */
    protected $first;
    /** @var Day|DayReference|Closure day definition or plain day object */
    protected $second;

    /**
     * Order of passed days or its definitions is irrelevant
     * @param Day|DayReference|Closure $first
     * @param Day|DayReference|Closure $second
     */
    public function __construct($first, $second)
    {
        $this->first = $first;
        $this->second = $second;
    }

    /**
     * Returns all unique days from the start to end day defined in constructor
     * (with these days)
     * @param Calendar $calendar
     * @param integer $year
     * @return array of Day objects
     */
    public function getDays(Calendar $calendar, $year)
    {
        $firstDay = $calendar->getDayFromDefinition($this->first, $year);
        $secondDay = $calendar->getDayFromDefinition($this->second, $year);

        if ($firstDay && $secondDay) {
            $days = static::getDaysBetween($firstDay, $secondDay, $year);
        } elseif ($firstDay) {
            $days = array($firstDay);
        } elseif ($secondDay) {
            $days= array($secondDay);
        } else {
            $days = array();
        }

        return $days;
    }

    /**
     * Returns an array of Day object between given Days (along with these days).
     * Order is irrelevant
     * @param Day $firstDay
     * @param Day $secondDay
     * @param integer $year
     */
    public static function getDaysBetween(Day $firstDay, Day $secondDay, $year)
    {
        list($startDay, $endDay) = static::order($firstDay, $secondDay);

        $daysBetween = array();
        $nextDay = $startDay;

        while ($nextDay->isEarlierThan($endDay)) {
            $daysBetween[] = $nextDay;
            $nextDay = $nextDay->next($year);
        }

        $daysBetween[] = $endDay;

        return $daysBetween;
    }

    /**
     * Orders days from earlier to later
     * @param Day $firstDay
     * @param Day $secondDay
     * @return array of Day objects sorted by date (ascending)
     */
    protected static function order(Day $firstDay, Day $secondDay)
    {
        if ($secondDay->isEarlierThan($firstDay)) {
            $earlierDay = $secondDay;
            $laterDay = $firstDay;
        } else {
            $earlierDay = $firstDay;
            $laterDay = $secondDay;
        }

        return array($earlierDay, $laterDay);
    }
}
