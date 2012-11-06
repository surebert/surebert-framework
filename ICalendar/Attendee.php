<?php

/**
 * Represents and Calendar meeting request attendee or organizer
 * @package sb_ICalendar
 * @author paul.visco@roswellpark.org
 */
namespace sb\ICalendar;

class Attendee
{

    /**
     * The display name of the attendee e.g Visco, Paul
     * @var string
     */
    public $dname;

    /**
     * The email of the attendee e.g. paul.visco@roswellpark.org
     * @var string
     */
    public $email;

    /**
     * Creates a new construct
     *
     * <code>
     * new \sb\ICalendar\Attendee(
     *      'Visco, Paul', 
     * 'paul.visco@roswellpark.org');
     * </code>
     *
     * @param string $dname The display name
     * @param string $email The email
     */
    public function __construct($dname, $email)
    {
        $this->dname = $dname;
        $this->email = $email;
    }

    /**
     * Returns the Attendee in iCalendar format
     * @return <type>
     */
    public function __toString()
    {
        return 'ATTENDEE;ROLE=REQ-PARTICIPANT;'
        .'PARTSTAT=NEEDS-ACTION;RSVP=TRUE;CN="'
        . $this->dname
        . '":MAILTO:' . $this->email;
    }
}

