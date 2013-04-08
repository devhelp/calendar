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
     */
    public function add(Day $day)
    {
        if (!$this->has($day)) {
            $this->days[] = $day;
        }
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
        foreach ($this->days as $dayInSet) {
            if ($dayInSet->equals($day)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns array of days from the set
     * @return array of Days
     */
    public function getDays()
    {
        return $this->days;
    }
}
