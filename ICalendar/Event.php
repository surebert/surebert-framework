<?php

/**
 * Used to create an .ics doc for an ICalendar event
 * RFC 2446 http://tools.ietf.org/html/rfc2446
 *
 * Tested with entouage, outlook, mail.app, owa, android, iphone, and blackbery
 *
 * <code>
 * $event = new \sb\ICalendar\Event();
 * $event->location = '901 Washington #3';
 * $event->summary = 'Ride for Roswell Meeting';
 * $event->setTime('11/26/2009 13:30', '11/26/2009 14:30');
 * $event->addAttendee(new \sb\ICalendar\Attendee('Reid, Delmar', 'del.reid@roswellpark.org'));
 * $event->addAttendee(new \sb\ICalendar\Attendee('Dean, Gregary', 'gregary.dean@roswellpark.org'));
 * $event->setOrganizer(new \sb\ICalendar\Organizer('Visco, Paul', 'paul.visco@roswellpark.org'));
 *
 * //view contents
 * echo $event->__toString();
 *
 * //force download ics file
 * $event->sendHtmlHeaders();
 * echo $event->__toString();
 *
 * //send via email
 * $event->send();
 *
 * //Canceling
 * $event = new \sb\ICalendar_Event($uid);
 * $event->summary = 'Ride for Roswell Meeting';
 * $event->addAttendee(new \sb\ICalendar\Attendee('Reid, Delmar', 'del.reid@roswellpark.org'));
 * $event->addAttendee(new \sb\ICalendar\Attendee('Dean, Gregary', 'gregary.dean@roswellpark.org'));
 * $event->setOrganizer(new \sb\ICalendar\Organizer('Visco, Paul', 'paul.visco@roswellpark.org'));
 *
 * $event->send();
 * </code>
 *
 * @package ICalendar
 * @author paul.visco@roswellpark.org, gregary.dean@roswellpark.org
 */
namespace sb\ICalendar;

class Event
{

    /**
     * The summary of the event
     * @var string
     */
    public $summary = '';

    /**
     * The location of the event
     * @var string
     */
    public $location = '';

    /**
     * The method that the event is sent as
     *
     * REQUEST = new event
     * CANCEL = cancel event
     * see rfc 2446 for more info http://tools.ietf.org/html/rfc2446#section-3.2
     *
     * @var string
     */
    public $method = 'REQUEST';

    /**
     * The unique event id
     * You need this id in order to cancel or update the event, it is also what
     * makes the event unique on a calendar.
     *
     * If not provided it is calculated from the md5 of the start and end time plus summary
     *
     * @var string
     */
    public $uid;

    /**
     * The start time of the event in any format strtotime can handle
     * @var string
     */
    protected $dtstart = '';

    /**
     * The end time of the event in any format strtotime can handle
     * @var string
     */
    protected $dtend = '';

    /**
     * The attendees An array of \sb\Calendar_Ics_Attendee
     * @var array
     */
    protected $attendees = Array();

    /**
     * The organizer
     * @var \sb\Calendar_Ics_Attendee
     */
    protected $organizer;

    /**
     * Set up the basic event parameters
     * @param string $uid The unqiue ID of the event, assigned if not provided
     * required for cancel, update
     */
    public function __construct($uid = '')
    {
        $this->uid = $uid;
    }

    /**
     * Set up the start/end time of the event
     * @param string $dtstart The begin time of the event in any format strtotime can handle
     * @param string $dtend The endtime of the event in any format strtotime can handle
     */
    public function setTime($dtstart, $dtend)
    {
        $this->dtstart = $dtstart;
        $this->dtend = $dtend;
    }

    /**
     * Adds the organizer of the event
     *
     * @param \sb\ICalendar_Attendee $attendee
     */
    public function setOrganizer(Organizer $attendee)
    {
        $this->organizer = $attendee;
    }

    /**
     * Adds an attendee to the event, required for viewing schedules
     * This does not mean that the event is sent to those attendees only that they
     * get notified when changes occurr
     *
     * @param \sb\ICalendar_Attendee $attendee
     */
    public function addAttendee(Attendee $attendee)
    {
        $this->attendees[] = $attendee;
    }

    /**
     * Sends HTML headers used to make browser recognize .ics file
     */
    public function sendHtmlHeaders()
    {

        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename=calendar.ics');
    }

    /**
     * Saves the ics packet as a file
     * @param string $file_path
     */
    public function toFile($file_path)
    {
        $ics = $this->__toString();
        file_put_contents($file_path, $ics);
    }

    /**
     * Send via email the subject is the first 20 chars of the summary,
     * the message is the summary.  The email is sent to the organizer's email,
     * The attendees all cc'd
     *
     * @return boolean
     */
    public function send()
    {
        $subject = 'EVENT';
        if ($this->method == 'CANCEL') {

            $subject = 'CANCELED ' . $subject;
            if (empty($this->uid)) {
                throw(new \Exception('Must set uid to cancel an event.'));
            }
        }

        if (!empty($this->summary)) {
            $subject .= ': ' . substr($this->summary, 0, 20) . '...';
        }

        $to = '"' . $this->organizer->dname . '" <' . $this->organizer->email . '>';

        $mail = new \sb\Email($to, $subject, $this->summary, $to);
        $attendee_emails = Array();
        foreach ($this->attendees as $attendee) {
            $attendee_emails[] = '"' . $attendee->dname . '" <' . $attendee->email . '>';
        }

        $mail->cc = $attendee_emails;
        $mail->add_ICalendar_Event($this);
        return $mail->send();
    }

    /**
     * Converts the Event object into a string in ICalendar .ics format
     * @return string
     */
    public function __toString()
    {

        if (empty($this->organizer)) {
            throw(new \Exception('You must add an event organizer'));
        }

        if (empty($this->attendees)) {
            throw(new \Exception('You must add at least one attendee'));
        }

        //convert to unix time
        $dtstart = strtotime($this->dtstart);
        $dtend = strtotime($this->dtend);

        $ics = Array();
        $ics[] = "BEGIN:VCALENDAR";
        $ics[] = "VERSION:2.0";
        $ics[] = "METHOD:" . $this->method;

        if ($this->method == 'CANCEL') {
            $ics[] = 'STATUS:CANCELLED';
        }

        $ics[] = "PRODID:-//surebert/ics//NONSGML v1.0//EN";
        $ics[] = "BEGIN:VEVENT";

        if (!empty($this->location)) {
            $ics[] = "LOCATION:" . $this->location;
        }

        if (isset($this->organizer)) {
            $ics[] = $this->organizer->__toString();
        }

        foreach ($this->attendees as $attendee) {
            $ics[] = $attendee->__toString();
        }

        $this->uid = empty($this->uid) ? md5($dtstart . $dtend . $this->summary) : $this->uid;
        $ics[] = "UID:" . $this->uid;
        $ics[] = "DTSTAMP:" . gmdate('Ymd') . 'T' . gmdate('His') . "Z";
        $ics[] = "DTSTART:" . gmdate('Ymd', $dtstart) . 'T' . gmdate('His',
                $dtstart) . "Z";
        $ics[] = "DTEND:" . gmdate('Ymd', $dtend) . 'T' . gmdate('His', $dtend) . "Z";
        $ics[] = "SUMMARY:" . $this->summary;

        $ics[] = "END:VEVENT";
        $ics[] = "END:VCALENDAR";

        return implode("\n", $ics);
    }
}

