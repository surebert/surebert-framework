<?php

/**
 * Represents and event organizer
 *
 * <code>
 * new \sb\ICalendar\Organizer('Visco, Paul', 'paul.visco@roswellpark.org')
 * </code>
 *
 * @package ICalendar
 * @author paul.visco@roswellpark.org
 */
namespace sb\ICalendar;

class Organizer extends Attendee
{

    /**
     * Returns the Organizer in iCalendar format
     *
     * @return string
     */
    public function __toString()
    {

        return "ORGANIZER;CN=\"" . $this->dname . "\":MAILTO:" . $this->email;
    }
}

