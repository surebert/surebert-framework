<?php

/**
 * used to read the weather from NOAA, requires sb_Weather_Feed
 *
 * @author Paul 05/21/2005
 * @version 2.11  12/08/2008
 * @package sb_Web
 * 
 */
class sb_Web_Weather{

	/**
	 * 
	 * Looks up the weather feed from the given feed url, There are other properties which can be accessed from the feed to see them uncomment print_r($xml) line. I would suggest always storing the json_encoded data in a session so that you only need to access the weather server once per visit as it can be slow and there is no need to check the weather more than once per visit.
	 *
	 * @param string $feed The url to the weather feed from NOAA e.g.
	 * @return object weather_feed
	 * <code>
	 * $weather = sb_Web_Weather::fetch('http://www.nws.noaa.gov/data/current_obs/KBUF.xml');
	 * print_r($weather);
	 * </code>
	 */
	public static function fetch($feed){

		$xml = simplexml_load_string(file_get_contents($feed));
	
		$weather = new sb_Web_WeatherFeed();
		if(is_object($xml)){
			$weather->condition = sprintf($xml->weather);
			$weather->temp_f = sprintf($xml->temp_f);
			$weather->temp_c = sprintf($xml->temp_c);
			$weather->wind_mph = sprintf($xml->wind_mph);
			$weather->icon = sprintf($xml->icon_url_base.$xml->icon_url_name);
		}
		
		return $weather;

	}
}

?>