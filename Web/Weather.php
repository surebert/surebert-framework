<?php
/**
 * used to read the weather from NOAA, requires sb_Weather_Feed
 *
 * @package Web
 * 
 */
namespace sb\Web;

class Weather{

    /**
     * 
     * Looks up the weather feed from the given feed url, There are other properties which can be accessed from the feed to see them uncomment print_r($xml) line. I would suggest always storing the json_encoded data in a session so that you only need to access the weather server once per visit as it can be slow and there is no need to check the weather more than once per visit.
     *
     * @param string $feed The url to the weather feed from NOAA e.g.
     * @return object weather_feed
     * <code>
     * $weather = \sb\Web_Weather::fetch('http://w1.weather.gov/xml/current_obs/KBUF.xml');
     * print_r($weather);
     * </code>
     */
    public static function fetch($feed)
    {
    
        $weather = new \sb\Web_WeatherFeed();
        $parts = parse_url($feed);
        $fp = @fsockopen($parts['host'], 80, $errno, $errstr, 2);
        if($fp) {
            // make request
            $out = "GET ".$parts['path']." HTTP/1.1\r\n";
            $out .= "Host: ".$parts['host']."\r\n";
            $out .= "Connection: Close\r\n\r\n";
            fwrite($fp, $out);

            // get response
            $resp = "";
            while (!feof($fp)) {
                $resp .= fgets($fp, 128);
            }
            fclose($fp);
            // check status is 200
            $status_regex = "/HTTP\/1\.\d\s(\d+)/";
            if(preg_match($status_regex, $resp, $matches) && $matches[1] == 200) {
                // load xml as object
                $parts = explode("\r\n\r\n", $resp);
                $xml = simplexml_load_string($parts[1]);

                if(is_object($xml)){
                    $weather->condition = sprintf($xml->weather);
                    $weather->temp_f = sprintf($xml->temp_f);
                    $weather->temp_c = sprintf($xml->temp_c);
                    $weather->wind_mph = sprintf($xml->wind_mph);
                    $weather->icon = sprintf($xml->icon_url_base.$xml->icon_url_name);
                }
            }
        }
        
        return $weather;

    }
}

