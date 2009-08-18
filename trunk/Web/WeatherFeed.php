<?php
/**
 * The properties of the weather reading that are returned
 *
 * @author: Paul 05/21/2005
 * @version 2.11  12/08/2008
 * @package Web
 * 
 */

class sb_Web_WeatherFeed{
	
	/**
	 * The current weather condition
	 * 
	 * @var string
	 */
	public $condition;
	/**
	 * The current temperature in fahrenheit
	 *
	 * @var integer
	 */
	public $temp_f;
	
	/**
	 * The current temperature in celcius
	 *
	 * @var integer
	 */
	public $temp_c;
	
	/**
	 * The current wind mph
	 *
	 * @var float
	 */
	public $wind_mph;
	
	/**
	 * The url of the icon that can be used with this
	 *
	 * @var string
	 */
	public $icon;
}


?>