Credits
-------

Plugin brought to you by : Devhelp.pl (http://devhelp.pl)

Purpose
-------

Purpose of Devhelp/Calendar package is to help creating calendar with easy
to define events that occur both on constant date as well as on date that
is somehow referenced from another event (or calculated using custom logic)

Is also helps defining days off that are referenced to certain day, event
or are calculated using custom logic

Is if for you ?
---------------
- Do you want to define calendar with events ?
- Do you want to store events that are related to each other ?
- Do you want to define events that occurs on different days depending on year or other events ?
- Do you want to have easy way of calculating days off ?

If one of the answer was "yes" then I hope you'll enjoy using this package

Installation
------------

### Composer

add package to composer.json

    "require" : {
        "devhelp/calendar": "dev-master"
    }

run update

    composer update devhelp/calendar

Usage
------------

#### Example calendar definition

    $definition = array(
        Calendar::DAYS_OFF => array(
            new Day(3, 1),
            new Day(4, 1),
            new Day(5, 1),
            new DayRange(new Day(3, 1), new Day(10, 1)),
            new DayReference('national/constitution-day'),
        ),
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
                'joanne' => new Day(23, 3),
                'paul' => new Day(31, 8),
                'margaret' => new DayReference('birthday/paul', '+2 day'),
            ),
            'specific-day' => array(
                'first-day-of-march' => new Day(1, 3),
                'last-day-of-february' => new DayReference('specific-day/first-day-of-march', '-1 day'),
                '7-august' => new Day(7, 8),
                '7-september' => new DayReference('specific-day/7-august', '+1 month'),            
            )
        )
    );

    $calendar = new \Devhelp\Calendar\Calendar($definition);

#### Get Event Day

for events that are defined by reference (with value other than 0)
or custom function you need to specify year to do the math

    $calendar->getDay('religion/easter', 2013);

for events referenced with simple reference or defined as day just
pass event name

    $calendar->getDay('birthday/joanne');

    $calendar->getDay('national/joining-european-union');

#### Get Days Off

you can get days off for defined in the calendar for certain year

    $calendar->getDaysOff(2013);

if no math needs to be done when calculating days off, then you
do not have to pass year