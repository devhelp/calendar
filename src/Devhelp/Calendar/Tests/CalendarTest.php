<?php

namespace Devhelp\Calendar\Tests;

use Devhelp\Calendar\Calendar;
use Devhelp\Calendar\Day;
use Devhelp\Calendar\DayReference;
use Devhelp\Calendar\DayRange;

class CalendarTest extends \PHPUnit_Framework_TestCase
{

    public function testGetEventDefinitionReturnsCorrectDefinition()
    {
        $day1 = new Day(1, 2);
        $day2 = new Day(1, 2);

        $testEvents = array();
        $testEvents['some-event'] = $day1;
        $testEvents['another-event'] = $day2;

        $eventsDefinition = array('test' => $testEvents);

        $definition = array(Calendar::EVENTS => $eventsDefinition);

        $calendar = new Calendar($definition);

        $this->assertEquals(null, $calendar->getEventDefinition(''));
        $this->assertEquals(null, $calendar->getEventDefinition(false));
        $this->assertEquals(null, $calendar->getEventDefinition(null));
        $this->assertEquals(null, $calendar->getEventDefinition(1));
        $this->assertEquals(null, $calendar->getEventDefinition('some-event'));
        $this->assertEquals(null, $calendar->getEventDefinition('group-not-set/not-set-event'));
        $this->assertEquals(null, $calendar->getEventDefinition('test/not-set-event'));
        $this->assertEquals($day1, $calendar->getEventDefinition('test/some-event'));
        $this->assertEquals($day2, $calendar->getEventDefinition('test/another-event'));
    }

    /**
     * @dataProvider providerGetDayWithValidDefinitions
     * @param type $event
     * @param type $expected
     */
    public function testGetDayWithValidDefinitions($event, $year, $expected)
    {
        $definition = $this->getCalendarDefinition();

        $calendar = new Calendar($definition);

        $day = $calendar->getDay($event, $year);

        if (is_null($expected)) {
            $this->assertEquals($expected, $day);
        } else {
            $this->assertTrue($expected->equals($day));
        }
    }

    public function providerGetDayWithValidDefinitions()
    {
        return array(
            array('national/joining-european-union', 2000, new Day(3, 5)),
            array('birthday/asia', 2000, new Day(23, 3)),
            array('birthday/pawel', 2000, new Day(31, 8)),
            array('birthday/gosia', 2000, new Day(2, 9)),
            array('national/independence-day', 2000, new Day(11, 11)),
            array('religion/easter', 2012, new Day(8, 4)),
            array('religion/easter', 2013, new Day(31, 3)),
            array('religion/divine-mercy-sunday', 2012, new Day(15, 4)),
            array('religion/divine-mercy-sunday', 2013, new Day(7, 4)),
            array('specific-day/last-day-of-february', 2012, new Day(29, 2)),
            array('specific-day/last-day-of-february', 2013, new Day(28, 2)),
            array('specific-day/7-september', 2012, new Day(7, 9)),
            array('specific-day/7-october', 2012, new Day(7, 10)),
            array('specific-day/7-december', 2012, new Day(7, 12)),
            array('specific-day/7-january', 2012, new Day(7, 1)),
            array('', 2012, null),
        );
    }

    /**
     * @dataProvider providerGetDayReturnsExceptionWhenEventReturnsUnsupportedType
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Either null or Devhelp\Calendar\Day object can be returned
     */
    public function testGetDayReturnsExceptionWhenEventReturnsUnsupportedType($event)
    {
        $definition = array(
            Calendar::EVENTS => array(
                'specific-day' => array(
                    'closure-one' => function () {
                        return true;
                    },
                    'closure-two' => function () {
                        return false;
                    }
                )
            )
        );

        $calendar = new Calendar($definition);

        $calendar->getDay($event, 0);
    }

    public function providerGetDayReturnsExceptionWhenEventReturnsUnsupportedType()
    {
        return array(
            array('specific-day/closure-one'),
            array('specific-day/closure-two'),
        );
    }

    /**
     * @dataProvider providerGetDate
     * @param type $event
     * @param type $expected
     */
    public function testGetDate($event, $year, $expected)
    {
        $definition = array(
            Calendar::EVENTS => array(
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

        $calendar = new Calendar($definition);

        $date = $calendar->getDate($event, $year);

        if (is_null($expected)) {
            $this->assertEquals($expected, $date);
        } else {
            $this->assertEquals($expected->getTimestamp(), $date->getTimestamp());
        }
    }

    public function providerGetDate()
    {
        return array(
            array(null, 2012, null),
            array('last-day-of-february', 2012, null),
            array('specific-day', 2013, null),
            array('specific-day/8-september', 2012, null),
            array('specific-day/last-day-of-february', 2012, new \DateTime('29 February 2012')),
            array('specific-day/last-day-of-february', 2013, new \DateTime('28 February 2013')),
            array('specific-day/7-september', 2012, new \DateTime('7 September 2012')),
            array('specific-day/7-october', 2012, new \DateTime('7 October 2012')),
            array('specific-day/7-december', 2012, new \DateTime('7 December 2012')),
            array('specific-day/7-january', 2012, new \DateTime('7 January 2012')),
        );
    }

    /**
     * @dataProvider providerGetDaysOffWithoutDayRange
     * @param type $event
     * @param type $expected
     */
    public function testGetDaysOffWithoutDayRange($year, $expected)
    {
        $definition = $this->getCalendarDefinition();

        $definition[Calendar::DAYS_OFF] = array(
            new Day(1, 2),
            new Day(2, 3),
            new DayReference('national/independence-day'),
            function ($calendar, $year) {
                return $year%2 ? new Day(5, 5) : null;
            }
        );

        $calendar = new Calendar($definition);

        $daysOff = $calendar->getDaysOff($year);

        $this->assertEquals(count($expected), count($daysOff), "Number of days does not match");

        foreach ($daysOff as $key => $day) {
            $this->assertTrue($expected[$key]->equals($day), "Day at position $key is different");
        }
    }

    public function providerGetDaysOffWithoutDayRange()
    {
        return array(
            array(2001, array(
                new Day(1, 2),
                new Day(2, 3),
                new Day(11, 11),
                new Day(5, 5)
            )),
            array(2000, array(
                new Day(1, 2),
                new Day(2, 3),
                new Day(11, 11)
            )),
        );
    }

    public function testGetDaysOffWithDayRange()
    {
        $definition = $this->getCalendarDefinition();

        $definition[Calendar::DAYS_OFF] = array(
            new Day(1, 1),
            new Day(2, 1),
            new Day(3, 1),
            new Day(4, 1),
            new Day(5, 1),
            new DayRange(new Day(3, 1), new Day(10, 1)),
            new DayRange(new Day(12, 1), new Day(1, 1))
        );

        $calendar = new Calendar($definition);

        $daysOff = $calendar->getDaysOff(2000);

        $expected = array(
            new Day(1, 1),
            new Day(2, 1),
            new Day(3, 1),
            new Day(4, 1),
            new Day(5, 1),
            new Day(6, 1),
            new Day(7, 1),
            new Day(8, 1),
            new Day(9, 1),
            new Day(10, 1),
            new Day(11, 1),
            new Day(12, 1),
        );

        $this->assertEquals(count($expected), count($daysOff), "Number of days does not match");

        foreach ($daysOff as $key => $day) {
            $this->assertTrue($expected[$key]->equals($day), "Day at position $key is different");
        }
    }
    
    public function testGetDefinition()
    {
        $definition = $this->getCalendarDefinition();

        $calendar = new Calendar($definition);
        
        $this->assertEquals($calendar->getDefinition(), $definition);
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
