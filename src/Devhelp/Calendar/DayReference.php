<?php

namespace Devhelp\Calendar;

/**
 * Stores reference for event and offset which is used to
 * calculate referenced day. 
 * 
 * Supported offset are:
 * - day, example: +3 day
 * - month, example: +1 month
 * 
 * @package devhelp/calendar
 * @author Paweł Barański <pawel.baranski1@gmail.com>
 */
class DayReference
{

    /** @var string event name*/
    protected $event;
    /** @var string offset string like "+3 day", "+1 month", etc.*/
    protected $offsetString;
    /** @var int offset value (calculated from offsetString)*/
    protected $offsetValue;
    /** @var string day|month (calculated from offsetString)*/
    protected $offsetUnit;
    
    protected static $defaultOffsetString = '+0 day';

    public function __construct($event, $offsetString = '')
    {
        $this->event = $event;
        $this->offsetString = $offsetString ? $offsetString : static::$defaultOffsetString;

        $offset = $this->parseOffset($this->offsetString);

        $this->offsetValue = (integer)$offset['value'];
        $this->offsetUnit = $offset['unit'];
    }

    /**
     * Returns referenced event name
     * @return string
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * Returns offset value
     * @return integer
     */
    public function getOffsetValue()
    {
        return $this->offsetValue;
    }

    /**
     * Returns offset unit
     * @return string day|month
     */
    public function getOffsetUnit()
    {
        return $this->offsetUnit;
    }

    /**
     * Returns offset string
     * @return string
     */
    public function getOffsetString()
    {
        return $this->offsetString;
    }

    /**
     * Parses offset string in order to validate and retireve
     * its value and unit
     * @param string $offsetString
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function parseOffset($offsetString)
    {
        $offset = array();

        $pattern = '~^([\+\-]{1})(\d+)\s(day|month)$~';

        preg_match($pattern, $offsetString, $matches);

        if (!$this->patternMatched($matches)) {
            throw new \InvalidArgumentException(
                sprintf(
                    "Invalid offset [%s], expected pattern is %s",
                    $offsetString,
                    $pattern
                )
            );
        }

        $offset['value'] = intval($matches[1] . $matches[2]);
        $offset['unit'] = $matches[3];

        return $offset;
    }

    /**
     * Checks if pattern has expected matches
     * @param type $matches
     * @return boolean
     */
    private function patternMatched($matches)
    {

        if (!@$matches[0] || (!@$matches[2] && $matches[2] !== '0') || !@$matches[3]) {
            return false;
        }

        return true;
    }

    /**
     * Returns referenced day from the calendar
     * @param Calendar $calendar calendar in which day has to be looked
     * @param integer $year year for which the day has to be calculated
     * @return null|Day
     */
    public function getReferencedDay(Calendar $calendar, $year = null)
    {
        $day = $calendar->getDay($this->getEvent(), $year);

        if (!$day) {
            return null;
        }
        
        if ($this->getOffsetValue() === 0) {
            $referencedDay = $day;
        } else {
            $dt = $day->toDateTime($year);
            $dt->modify($this->getOffsetString());
            $referencedDay = Day::fromDateTime($dt);
        }
        
        return $referencedDay;
    }
}
