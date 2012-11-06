<?php
/**
 * The properties of the weather reading that are returned
 *
 * @author paul.visco@roswellpark.org
 * @package Web
 * 
 */
namespace sb\Web;

class WeatherFeed{
    
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

