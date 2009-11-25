<?php
/**
 * Represents and event organizer
 */
class sb_ICalendar_Organizer extends Attendee{

	/**
	 * Returns the Oragnizer in iCalendar format
	 * @return <type>
	 */
	public function  __toString() {
		return "ORGANIZER;CN=\"".$this->dname."\":MAILTO:".$this->email;
	}
}
?>