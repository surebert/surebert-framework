<?php

class sb_ICalendar_Event{

	/**
	 * The summary of the event
	 * @var string
	 */
	protected $summary ='';

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
	 * The attendees An array of sb_Calendar_Ics_Attendee
	 * @var array
	 */
	protected $attendees = Array();

	/**
	 * The organizer
	 * @var sb_Calendar_Ics_Attendee
	 */
	protected $organizer;

	/**
	 * Set up the basic event
	 * @param <type> $summary
	 * @param <type> $dtstart
	 * @param <type> $dtend
	 * @param <type> $location
	 */
	public function __construct($summary='', $dtstart='', $dtend='', $location=''){
		$this->summary = $summary;
		$this->dstart = $dtstart;
		$this->dtend = $dtend;
		$this->location = $location;

	}

	public function set_time($dtstart, $dtend){
		$this->dtstart = $dtstart;
		$this->dtend = $dtend;
	}

	public function set_organizer(sb_Calendar_Ics_Attendee $attendee){
		$this->organizer = $attendee;
	}

	public function add_attendee(sb_Calendar_Ics_Attendee $attendee){
		$this->attendee[] = $attendee;
	}

	public function  __toString() {

		//convert to unix time
		$dtstart = strtotime($this->dtstart);
		$dtend = strtotime($this->dtend);

		$ics = Array();
		$ics[] = "BEGIN:VCALENDAR";
		$ics[] = "VERSION:2.0";
		$ics[] = "METHOD:REQUEST";
		$ics[] = "PRODID:-//surebert/ics//NONSGML v1.0//EN";
		$ics[] = "BEGIN:VEVENT";
		$ics[] = ''.$this->organizer->dname;

		foreach($this->attendees as $attendee){
			$ics[] = ''.$attendee;
		}

		$ics[] = "UID:" . md5($dtstart.$dtend.$this->summary);
		$ics[] = "DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z";
		$ics[] = "DTSTART:" . gmdate('Ymd', $dtstart).'T'. gmdate('His', $dtstart) . "Z";
		$ics[] = "DTEND:" . gmdate('Ymd', $dtend).'T'. gmdate('His', $dtend) . "Z";
		$ics[] = "SUMMARY:".$this->summary;
		$ics[] = "END:VEVENT";
		$ics[] = "END:VCALENDAR";

		return implode("\n", $ics);
	}
}
?>