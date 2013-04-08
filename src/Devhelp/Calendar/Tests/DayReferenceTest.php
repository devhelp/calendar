<?php

namespace Devhelp\Calendar\Tests;

use Devhelp\Calendar\DayReference;
use Devhelp\Calendar\Day;
use Devhelp\Calendar\Calendar;

class DayReferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerExceptionIsThrownWhenOffsetIsInvalid
     * @expectedException \InvalidArgumentException
     */
    public function testExceptionIsThrownWhenOffsetIsInvalid($offsetString)
    {
        new DayReference('some-event', $offsetString);
    }

    public function providerExceptionIsThrownWhenOffsetIsInvalid()
    {
        return array(
            array('+1 year'),
            array('10'),
            array('day'),
            array('month'),
            array('1 dayx'),
            array('+1 dayx'),
            array('x1 day'),
            array('+1.00 day'),
            array('+1,00 day'),
            array('-10.0 day'),
            array('-10.0 day'),
            array('1.00 day'),
            array('1,00 day'),
        );
    }

    /**
     * @dataProvider providerValidOffsetIsParsedCorrectly
     */
    public function testValidOffsetIsParsedCorrectly($offsetString, $expected)
    {
        $dayReference = new DayReference('some-event', $offsetString);

        $this->assertEquals($expected[0], $dayReference->getOffsetValue());
        $this->assertEquals($expected[1], $dayReference->getOffsetUnit());
    }

    public function providerValidOffsetIsParsedCorrectly()
    {
        return array(
            array('+0 day', array(0, 'day')),
            array('+1 month', array(1, 'month')),
            array('+100 day', array(100, 'day')),
            array('-1 month', array(-1, 'month')),
            array('-100 day', array(-100, 'day')),
        );
    }

    /**
     * @dataProvider providerGetReferencedDay
     * @param type $referencedDay
     * @param type $year
     * @param type $expected
     */
    public function testGetReferencedDay($referencedDay, $year, $expected)
    {
        $definition = $this->getCalendarDefinition();

        $calendar = new Calendar($definition);

        $day = $referencedDay->getReferencedDay($calendar, $year);

        if (is_null($expected)) {
            $this->assertEquals($expected, $day);
        } else {
            $this->assertTrue($expected->equals($day));
        }
    }

    public function providerGetReferencedDay()
    {
        return array(
            array(new DayReference('national/constitution-day'), 2000, new Day(3, 5)),
            array(new DayReference('birthday/asia'), 2000, new Day(23, 3)),
            array(new DayReference('birthday/pawel', '+2 day'), 2000, new Day(2, 9)),
            array(new DayReference('religion/divine-mercy-sunday'), 2012, new Day(15, 4)),
            array(new DayReference('religion/divine-mercy-sunday'), 2013, new Day(7, 4)),
            array(new DayReference('specific-day/first-day-of-march', '-1 day'), 2012, new Day(29, 2)),
            array(new DayReference('specific-day/first-day-of-march', '-1 day'), 2013, new Day(28, 2)),
            array(new DayReference('specific-day/7-august', '+1 month'), 2012, new Day(7, 9)),
            array(new DayReference('specific-day/7-september', '+1 month'), 2012, new Day(7, 10)),
            array(new DayReference('specific-day/7-november', '+1 month'), 2012, new Day(7, 12)),
            array(new DayReference('specific-day/7-october', '+2 month'), 2012, new Day(7, 12)),
            array(new DayReference(''), 2012, null),
        );
    }

    protected function getCalendarDefinition()
    {
        $definition = array(
            Calendar::EVENTS => array(
                'religion' => array(
                    'easter' => function (Calendar $calendar, $year) {
                        $a = $year % 19;
                        $b = intval($year / 100);
                        $c = $year % 100;
                        $d = intval($b / 4);
                        $e = $b % 4;
                        $f = intval(($b + 8) / 25);
                        $g = intval(($b - $f + 1) / 3);
                        $h = (19 * $a + $b - $d - $g + 15) % 30;
                        $i = intval($c / 4);
                        $k = $c % 4;
                        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
                        $m = intval(($a + 11 * $h + 22 * $l) / 451);
                        $p = ($h + $l - 7 * $m + 114) % 31;
                        $day = $p + 1;
                        $month = intval(($h + $l - 7 * $m + 114) / 31);

                        return new Day($day, $month);
                    },
                    'divine-mercy-sunday' => new DayReference('religion/easter', '+7 day'),
                ),
                'national' => array(
                    'independence-day' => new Day(11, 11),
                    'constitution-day' => new Day(3, 5),
                    'joining-european-union' => new DayReference('national/constitution-day')
                ),
                'birthday' => array(
                    'asia' => new Day(23, 3),
                    'pawel' => new Day(31, 8),
                    'gosia' => new DayReference('birthday/pawel', '+2 day'),
                ),
                'specific-day' => array(
                    'first-day-of-march' => new Day(1, 3),
                    'last-day-of-february' => new DayReference('specific-day/first-day-of-march', '-1 day'),
                    '7-august' => new Day(7, 8),
                    '7-september' => new DayReference('specific-day/7-august', '+1 month'),
                    '7-october' => new DayReference('specific-day/7-september', '+1 month'),
                    '7-november' => new Day(7, 11),
                    '7-december' => new DayReference('specific-day/7-november', '+1 month'),
                    '7-january' => new DayReference('specific-day/7-november', '+2 month'),
                )
            )
        );

        return $definition;
    }
}
