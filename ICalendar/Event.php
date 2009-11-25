<?php
/**
 * Used to create an .ics doc for an ICalendar event
 *
 * Tested with entouage, outlook, mail.app, owa
 *
 * @author paul.visco@roswellpark.org
 */
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
	 * Set up the basic event parameters
	 * @param string $summary A summary of the event
	 * @param string $dtstart The begin time of the event in any format strtotime can handle
	 * @param string $dtend The endtime of the event in any format strtotime can handle
	 * @param string $location Optional The location of the event
	 */
	public function __construct($summary, $dtstart, $dtend, $location=''){
		$this->summary = $summary;
		$this->dstart = $dtstart;
		$this->dtend = $dtend;
		$this->location = $location;

	}

	public function set_time($dtstart, $dtend){
		$this->dtstart = $dtstart;
		$this->dtend = $dtend;
	}

	/**
	 * Adds the organizer of the event
	 *
	 * @param sb_ICalendar_Attendee $attendee
	 */
	public function set_organizer(sb_ICalendar_Organizer $attendee){
		$this->organizer = $attendee;
	}

	/**
	 * Adds an attendee to the event, required for viewing schedules
	 * This does not mean that the event is sent to those attendees only that they
	 * get notified when changes occurr
	 *
	 * @param sb_ICalendar_Attendee $attendee
	 */
	public function add_attendee(sb_ICalendar_Attendee $attendee){
		$this->attendees[] = $attendee;
	}

	/**
	 * Sends HTML headers used to make browser recognize .ics file
	 */
	public function send_html_headers(){

		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=calendar.ics');
	}

	/**
	 * Saves the ics packet as a file
	 * @param string $file_path
	 */
	public function to_file($file_path){
		$ics = $this->__toString();
		file_put_contents($file_path, $ics);
	}

	/**
	 * Converts the Event object into a string in ICalendar .ics format
	 * @return string
	 */
	public function  __toString() {

		if(empty($this->attendees)){
			throw(new Exception('You must add at least one attendee'));
		}
		
		//convert to unix time
		$dtstart = strtotime($this->dtstart);
		$dtend = strtotime($this->dtend);

		$ics = Array();
		$ics[] = "BEGIN:VCALENDAR";
		$ics[] = "VERSION:2.0";
		$ics[] = "METHOD:REQUEST";
		$ics[] = "PRODID:-//surebert/ics//NONSGML v1.0//EN";
		$ics[] = "BEGIN:VEVENT";

		if(!empty($location)){
			$ics[] = "LOCATION:".$this->location;
		}

		if(isset($this->organizer)){
			$ics[] = $this->organizer->__toString();
		}

		foreach($this->attendees as $attendee){
			$ics[] = $attendee->__toString();
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