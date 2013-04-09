<?php

namespace Devhelp\Calendar;

/**
 * Ensures uniqueness of the days stored in the classes instances
 * 
 * @package devhelp/calendar
 * @author Paweł Barański <pawel.baranski1@gmail.com>
 */
class DaySet
{
    /** @var array of Day objects*/
    protected $days;

    public function __construct(array $days = array())
    {
        $this->days = array();

        $this->addAll($days);
    }

    /**
     * Adds Day to a set if it does not already exist
     * @param Day $day
     * @return booelan true if Day was added, false if it wasn't
     */
    public function add(Day $day)
    {
        $addedSuccessful = false;

        if (!$this->has($day)) {
            $this->days[$day->getMonth()][$day->getDay()] = $day;
            $addedSuccessful = true;
        }

        return $addedSuccessful;
    }

    /**
     * Adds all days, that are not already in it, to the set 
     * @param array $days array of Day objects
     */
    public function addAll(array $days)
    {
        foreach ($days as $day) {
            $this->add($day);
        }
    }

    /**
     * Checks if given day exists in the set
     * @param Day $day
     * @return boolean
     */
    public function has(Day $day)
    {
        if (isset($this->days[$day->getMonth()][$day->getDay()])) {
            return true;
        }
        
        return false;
    }

    /**
     * Returns array of days from the set.
     * Days are in asceding order
     * @return array of Days
     */
    public function getDays()
    {
        return $this->flattenDaysArray();
    }
    
    protected function flattenDaysArray()
    {
        $days = array();
        
        foreach ($this->days as $daysInMonth) {
            foreach ($daysInMonth as $day) {
                $days[] = $day;
            }
        }
        
        return $days;
    }
}
