<?php

namespace Devhelp\Calendar;

/**
 * Calendar class with days-off and events definitions. Example definition that
 * you can pass to the calendar looks like this:
 * <pre>
 *  <code>
 * $definition = array(
 *          Calendar::DAYS_OFF => array(
 *              new Day(2, 3),
 *              new DayReference('national/independence-day'),
 *              function($calendar, $year) {
 *                  return $year%2 ? new Day(5, 5) : null;
 *              },
 *              new DayRange(new Day(1, 1), new Day(20, 1)),
 *          ),
 *          Calendar::EVENTS => array(
 *              'religion' => array(
 *                  'easter' => function(Calendar $calendar, $year) {
 *                      $a = $year % 19;
 *                      $b = intval($year / 100);
 *                      $c = $year % 100;
 *                      $d = intval($b / 4);
 *                      $e = $b % 4;
 *                      $f = intval(($b + 8) / 25);
 *                      $g = intval(($b - $f + 1) / 3);
 *                      $h = (19 * $a + $b - $d - $g + 15) % 30;
 *                      $i = intval($c / 4);
 *                      $k = $c % 4;
 *                      $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
 *                      $m = intval(($a + 11 * $h + 22 * $l) / 451);
 *                      $p = ($h + $l - 7 * $m + 114) % 31;
 *                      $day = $p + 1;
 *                      $month = intval(($h + $l - 7 * $m + 114) / 31);
 *
 *                      return new Day($day, $month);
 *                  },
 *                  'divine-mercy-sunday' => new DayReference('religion/easter', '+7 day'),
 *              ),
 *              'national' => array(
 *                  'independence-day' => new Day(11, 11),
 *              ),
 *              'birthday' => array(
 *                  'pawel' => new Day(31, 8),
 *                  'gosia' => new DayReference('birthday/pawel', '+2 day'),
 *              ),
 *              'specific-day' => array(
 *                  'first-day-of-march' => new Day(1, 3),
 *                  'last-day-of-february' => new DayReference('specific-day/first-day-of-march', '-1 day'),
 *              )
 *          )
 *      );
 *  </code>
 * </pre>
 * 
 * @package devhelp/calendar
 * @author Paweł Barański <pawel.baranski1@gmail.com>
 */
class Calendar
{

    const DAYS_OFF = 'days-off';
    const EVENTS = 'events';

    /** @var array definition of the calendar*/
    protected $definition;

    public function __construct(array $definition)
    {
        $this->definition = $definition;
    }

    /**
     * Get days off in given year. Days returned by the function are unique
     * @param integer $year year for which days off should be calculated
     * @return array of Day objects
     */
    public function getDaysOff($year = null)
    {
        $daysOff = new DaySet();

        foreach ($this->definition[static::DAYS_OFF] as $eventDefinition) {
            if ($eventDefinition instanceof DayRange) {
                $daysOff->addAll($eventDefinition->getDays($this, $year));
            } elseif (($day = $this->getDayFromDefinition($eventDefinition, $year))) {
                $daysOff->add($day);
            }
        }

        return $daysOff->getDays();
    }

    /**
     * Returns day from the given event defitnion. It supports days defined
     * as Day, DayReference and Closure objects
     * @param mixed $eventDefinition
     * @param integer $year for which day should be calculated
     * @return Day|null
     * @throws \RuntimeException
     */
    public function getDayFromDefinition($eventDefinition, $year = null)
    {
        if ($eventDefinition instanceof Day) {
            $day = $eventDefinition;
        } elseif ($eventDefinition instanceof DayReference) {
            $day = $eventDefinition->getReferencedDay($this, $year);
        } elseif ($eventDefinition instanceof \Closure) {
            $day = $eventDefinition($this, $year);
        } else {
            $day = null;
        }

        if (!is_null($day) && !($day instanceof Day)) {
            throw new \RuntimeException("Either null or Devhelp\Calendar\Day object can be returned");
        }

        return $day;
    }

    /**
     * Returns calendar definition
     * @return array
     */
    public function getDefinition()
    {
        return $this->definition;
    }

    /**
     * Get day for given event in given year
     * @param string $event
     * @param integer $year
     * @return null|Day
     */
    public function getDay($event, $year = null)
    {
        $day = $this->getDayFromDefinition($this->getEventDefinition($event), $year);

        return $day;
    }

    /**
     * dame as getDay but returns DateTime object
     * @param string $event
     * @param integer $year
     * @return null|\DateTime
     */
    public function getDate($event, $year)
    {
        $day = $this->getDay($event, $year);

        if (!$day) {
            return null;
        }

        return $day->toDateTime($year);
    }

    /**
     * Returns definition linked with given event
     * @param string $event
     * @return null|Day|DayReference|\Closure
     */
    public function getEventDefinition($event)
    {
        $exploded = explode('/', $event);

        if (count($exploded) == 2) {
            list($group, $name) = $exploded;
            return @$this->definition[static::EVENTS][$group][$name];
        } else {
            return null;
        }
    }
}
