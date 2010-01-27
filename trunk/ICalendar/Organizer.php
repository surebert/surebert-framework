<?php
/**
 * Represents and event organizer
 *
 * <code>
 * new sb_ICalendar_Organizer('Visco, Paul', 'paul.visco@roswellpark.org')
 * </code>
 *
 * @package sb_ICalendar
 * @author paul.visco@roswellpark.org
 */
class sb_ICalendar_Organizer extends sb_ICalendar_Attendee{

	/**
	 * Returns the Organizer in iCalendar format
	 *
	 * @return string
	 */
	public function  __toString() {

		return "ORGANIZER;CN=\"".$this->dname."\":MAILTO:".$this->email;
	}
}
?>