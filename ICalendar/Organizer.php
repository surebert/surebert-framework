<?php
/**
 * Represents and event organizer
 * @package sb_ICalendar
 * @author paul.visco@roswellpark.org
 */
class sb_ICalendar_Organizer extends sb_ICalendar_Attendee{

	/**
	 * Returns the Oragnizer in iCalendar format
	 * @return string
	 */
	public function  __toString() {

		return "ORGANIZER;CN=\"".$this->dname."\":MAILTO:".$this->email;
	}
}
?>