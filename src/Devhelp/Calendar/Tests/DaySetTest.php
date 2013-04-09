<?php

namespace Devhelp\Calendar\Tests;

use Devhelp\Calendar\Day;
use Devhelp\Calendar\DaySet;

class DaySetTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerIfDaySetStoresOnlyUniqueDays
     */
    public function testIfDaySetStoresOnlyUniqueDays($days, $expected)
    {
        $set = new DaySet($days);
        $setDays = $set->getDays();

        $this->assertEquals(count($expected), count($setDays));

        foreach ($setDays as $key => $day) {
            $this->assertTrue($expected[$key]->equals($day));
        }
    }

    public function providerIfDaySetStoresOnlyUniqueDays()
    {
        return array(
            array(
                array(),
                array(),
            ),
            array(
                array(
                    new Day(1, 1),
                    new Day(2, 1),
                ),
                array(
                    new Day(1, 1),
                    new Day(2, 1),
                ),
            ),
            array(
                array(
                    new Day(1, 1),
                    new Day(1, 1),
                    new Day(2, 1),
                    new Day(3, 1),
                ),
                array(
                    new Day(1, 1),
                    new Day(2, 1),
                    new Day(3, 1),
                ),
            ),
            array(
                array(
                    new Day(1, 5),
                    new Day(1, 4),
                    new Day(2, 3),
                    new Day(3, 2),
                ),
                array(
                    new Day(1, 5),
                    new Day(1, 4),
                    new Day(2, 3),
                    new Day(3, 2),
                ),
            ),
        );
    }
    
    /**
     * @dataProvider providerAddReturnsProperBoolean
     */
    public function testAddReturnsProperBoolean($day, $expected)
    {
        $days = array(
            new Day(1, 1),
            new Day(2, 1),
        );
        
        $set = new DaySet($days);
        
        $this->assertEquals($expected, $set->add($day));
    }
    
    public function providerAddReturnsProperBoolean()
    {
        return array(
            array(new Day(1, 1), false),
            array(new Day(2, 2), true),
        );
    }
}
