<?php

namespace Devhelp\Calendar\Tests;

use Devhelp\Calendar\DayRange;
use Devhelp\Calendar\Calendar;
use Devhelp\Calendar\Day;
use Devhelp\Calendar\DayReference;

class DayRangeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider providerGetDaysBetween
     * @param type $day1
     * @param type $day2
     * @param type $expected
     */
    public function testGetDaysBetween($day1, $day2, $year, $expected)
    {
        $daysBetween = DayRange::getDaysBetween($day1, $day2, $year);

        $this->assertEquals(count($expected), count($daysBetween));

        foreach ($daysBetween as $key => $day) {
            $this->assertTrue($expected[$key]->equals($day));
        }
    }

    public function providerGetDaysBetween()
    {
        return array(
            array(new Day(1, 1), new Day(10, 1), 2000, array(
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

            )),
            array(new Day(10, 1), new Day(1, 1), 2000, array(
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

            )),
            array(new Day(1, 1), new Day(1, 1), 2000, array(
                new Day(1, 1),
            )),
            array(new Day(28, 2), new Day(1, 3), 2012, array(
                new Day(28, 2),
                new Day(29, 2),
                new Day(1, 3),
            )),
            array(new Day(28, 2), new Day(1, 3), 2013, array(
                new Day(28, 2),
                new Day(1, 3),
            ))
        );
    }

    /**
     * @dataProvider providerGetDays
     * @param type $day1
     * @param type $day2
     * @param type $expected
     */
    public function testGetDays($day1, $day2, $year, $expected)
    {
        $calendar = new Calendar($this->getCalendarDefinition());

        $range = new DayRange($day1, $day2);

        $days = $range->getDays($calendar, $year);

        $this->assertEquals(count($expected), count($days));

        foreach ($days as $key => $day) {
            $this->assertTrue($expected[$key]->equals($day));
        }
    }

    public function providerGetDays()
    {
        return array(
            array(
                new Day(1, 1),
                new Day(10, 1),
                2000,
                array(
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

            )),
            array(
                new Day(10, 1),
                new Day(1, 1),
                2000,
                array(
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
            )),
            array(
                new Day(1, 1),
                new Day(1, 1),
                2000,
                array(
                    new Day(1, 1),
            )),
            array(
                new Day(28, 2),
                new Day(1, 3),
                2012,
                array(
                    new Day(28, 2),
                    new Day(29, 2),
                    new Day(1, 3),
            )),
            array(
                new Day(28, 2),
                new Day(1, 3),
                2013,
                array(
                    new Day(28, 2),
                    new Day(1, 3),
            )),
            array(
                new DayReference('religion/easter'),
                new DayReference('religion/easter', '+7 day'),
                2013,
                array(
                    new Day(31, 3),
                    new Day(1, 4),
                    new Day(2, 4),
                    new Day(3, 4),
                    new Day(4, 4),
                    new Day(5, 4),
                    new Day(6, 4),
                    new Day(7, 4),
            )),
            array(
                new DayReference('birthday/gosia'),
                new DayReference('birthday/pawel'),
                2013,
                array(
                    new Day(31, 8),
                    new Day(1, 9),
                    new Day(2, 9),
            )),
            array(
                new DayReference(''),
                new DayReference('birthday/pawel'),
                2013,
                array(
                    new Day(31, 8),
            )),
            array(
                new DayReference('birthday/gosia'),
                new DayReference(''),
                2013,
                array(
                    new Day(2, 9),
            )),
            array(
                new DayReference(''),
                new DayReference(''),
                2013,
                array()),
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
