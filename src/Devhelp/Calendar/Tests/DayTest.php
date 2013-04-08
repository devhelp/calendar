<?php

namespace Devhelp\Calendar\Tests;

use Devhelp\Calendar\Day;

class DayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerExceptionIsThrownWhenMonthIsInvalid
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 500
     */
    public function testExceptionIsThrownWhenMonthIsInvalid($month)
    {
        new Day(1, $month);
    }

    public function providerExceptionIsThrownWhenMonthIsInvalid()
    {
        return array(
            array(-1),
            array(13),
        );
    }

    /**
     * @dataProvider providerExceptionIsThrownWhenDayIsInvalid
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 501
     */
    public function testExceptionIsThrownWhenDayIsInvalid($day)
    {
        new Day($day, 1);
    }

    public function providerExceptionIsThrownWhenDayIsInvalid()
    {
        return array(
            array(-1),
            array(32),
        );
    }

    /**
     * @dataProvider providerExceptionIsThrownWhenDayIsInvalidForMonth
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 502
     */
    public function testExceptionIsThrownWhenDayIsInvalidForMonth($day, $month)
    {
        new Day($day, $month);
    }

    public function providerExceptionIsThrownWhenDayIsInvalidForMonth()
    {
        return array(
            array(30, 2),
            array(31, 2),
            array(31, 4),
            array(31, 6),
            array(31, 9),
            array(31, 11),
        );
    }

    /**
     * @dataProvider providerEquals
     */
    public function testEquals($day1, $day2, $equals)
    {
        $this->assertEquals($day1->equals($day2), $equals);
        $this->assertEquals($day2->equals($day1), $equals);
    }

    public function providerEquals()
    {
        return array(
            array(new Day(1, 2), new Day(1, 2), true),
            array(new Day(2, 1), new Day(2, 1), true),
            array(new Day(1, 2), new Day(2, 1), false),
            array(new Day(10, 10), new Day(1, 1), false),
            array(new Day(10, 10), new Day(1, 1), false),
        );
    }

    /**
     * @dataProvider providerToDateTime
     */
    public function testToDateTime($day, $year, $expected)
    {
        $this->assertEquals($expected->getTimestamp(), $day->toDateTime($year)->getTimestamp());
    }

    public function providerToDateTime()
    {
        return array(
            array(new Day(1, 2), 2012, new \DateTime('1 February 2012')),
            array(new Day(29, 2), 2012, new \DateTime('29 February 2012')),
            array(new Day(28, 2), 2013, new \DateTime('28 February 2013')),
        );
    }

    /**
     * @dataProvider providerIsEarlierThan
     */
    public function testIsEarlierThan($day1, $day2, $expected)
    {
        $this->assertEquals($expected, $day1->isEarlierThan($day2));
    }

    public function providerIsEarlierThan()
    {
        return array(
            array(new Day(2, 2), new Day(2, 3), true),
            array(new Day(2, 2), new Day(3, 2), true),
            array(new Day(2, 2), new Day(2, 1), false),
            array(new Day(2, 2), new Day(1, 2), false),
        );
    }

    /**
     * @dataProvider providerNext
     */
    public function testNext($day, $year, $expected)
    {
        $this->assertTrue($expected->equals($day->next($year)));
    }

    public function providerNext()
    {
        return array(
            array(new Day(2, 2), 2000, new Day(3, 2)),
            array(new Day(31, 1), 2000, new Day(1, 2)),
            array(new Day(31, 12), 2000, new Day(1, 1)),
        );
    }
}
