<?php
/**
 * Describes the parameters returned from the govt weather SOAP service
 * @author paul.visco@roswellpark.org
 * @package Web_SOAP
 */
namespace sb;

class Web_SOAP_WeatherGovParametersType 
    {
  public $maxt; // boolean
  public $mint; // boolean
  public $temp; // boolean
  public $dew; // boolean
  public $pop12; // boolean
  public $qpf; // boolean
  public $sky; // boolean
  public $snow; // boolean
  public $wspd; // boolean
  public $wdir; // boolean
  public $wx; // boolean
  public $waveh; // boolean
  public $icons; // boolean
  public $rh; // boolean
  public $appt; // boolean
  public $incw34; // boolean
  public $incw50; // boolean
  public $incw64; // boolean
  public $cumw34; // boolean
  public $cumw50; // boolean
  public $cumw64; // boolean
  public $conhazo; // boolean
  public $ptornado; // boolean
  public $phail; // boolean
  public $ptstmwinds; // boolean
  public $pxtornado; // boolean
  public $pxhail; // boolean
  public $pxtstmwinds; // boolean
  public $ptotsvrtstm; // boolean
  public $pxtotsvrtstm; // boolean
  public $tmpabv14d; // boolean
  public $tmpblw14d; // boolean
  public $tmpabv30d; // boolean
  public $tmpblw30d; // boolean
  public $tmpabv90d; // boolean
  public $tmpblw90d; // boolean
  public $prcpabv14d; // boolean
  public $prcpblw14d; // boolean
  public $prcpabv30d; // boolean
  public $prcpblw30d; // boolean
  public $prcpabv90d; // boolean
  public $prcpblw90d; // boolean
  public $precipa_r; // boolean
  public $sky_r; // boolean
  public $temp_r; // boolean
  public $td_r; // boolean
  public $wdir_r; // boolean
  public $wspd_r; // boolean
  public $wwa; // boolean
  public $wgust; // boolean
}


/**
 * Web_SOAP_WeatherGov class  This is used to lookup weather information
 *
 * @author paul.visco@roswellpark.org
 * @package Web_SOAP
 * 
 * <code>
 * $weather = new \sb\Web_SOAP_WeatherGov();
 * $result = $weather->LatLonListZipCode(14209);
 * </code>
 */
class Web_SOAP_WeatherGov extends SoapClient 
    {

  private static $classmap = array(
                                    'weatherParametersType' => '\sb\Web_SOAP_WeatherGovParametersType',
                                   );

  public function __construct($wsdl = "http://www.weather.gov/forecasts/xml/SOAP_server/ndfdXMLserver.php?wsdl", $options = array()) 
    {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   * Returns National Weather Service digital weather forecast data.  Supports latitudes and
   * longitudes for the Continental United States, Alaska, Hawaii, Guam, and Puerto Rico only.
   * Allowable values for the input variable "product" are "time-series" and "glance".  For
   * both products, a start and end time (Local) are required.  For the time-series product,
   * the input variable "weatherParameters" has array elements set to "true" to indicate which
   * weather parameters are being requested.  If an array element is set to "false", data for
   * that weather parameter are not to be returned.
   *
   * @param decimal $latitude
   * @param decimal $longitude
   * @param string $product
   * @param dateTime $startTime
   * @param dateTime $endTime
   * @param weatherParametersType $weatherParameters
   * @return string
   */
  public function NDFDgen($latitude, $longitude, $product, $startTime, $endTime, weatherParametersType $weatherParameters) 
    {
    return $this->__soapCall('NDFDgen', array($latitude, $longitude, $product, $startTime, $endTime, $weatherParameters),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns National Weather Service digital weather forecast data.  Supports latitudes and
   * longitudes for the Continental United States, Alaska, Hawaii, Guam, and Puerto Rico only.
   *  The latitude and longitude are delimited by a comma and multiple pairs are delimited
   * by a space (i.e. 30.00,-77.00 40.00,-90.00). Allowable values for the input variable "product"
   * are "time-series" and "glance".  For both products, a start and end time (Local) are required.
   *  For the time-series product, the input variable "weatherParameters" has array elements
   * set to "true" to indicate which weather parameters are being requested.  If an array element
   * is set to "false", data for that weather parameter are not to be returned.
   *
   * @param string $listLatLon
   * @param string $product
   * @param dateTime $startTime
   * @param dateTime $endTime
   * @param weatherParametersType $weatherParameters
   * @return string
   */
  public function NDFDgenLatLonList($listLatLon, $product, $startTime, $endTime, weatherParametersType $weatherParameters) 
    {
    return $this->__soapCall('NDFDgenLatLonList', array($listLatLon, $product, $startTime, $endTime, $weatherParameters),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns a list of Latitude and longitude pairs for a rectangle defined by its lower left
   * and upper right points.  Provides points in a format suitable for use in calling multi-point
   * functions NDFDgenLatLonList and NDFDgenByDayLatLonList.  Supports latitudes and longitudes
   * for the Continental United States, Alaska, Hawaii, Guam, and Puerto Rico.
   *
   * @param decimal $lowerLeftLatitude
   * @param decimal $lowerLeftLongitude
   * @param decimal $upperRightLatitude
   * @param decimal $upperRightLongitude
   * @param decimal $resolution
   * @return string
   */
  public function LatLonListSubgrid($lowerLeftLatitude, $lowerLeftLongitude, $upperRightLatitude, $upperRightLongitude, $resolution) 
    {
    return $this->__soapCall('LatLonListSubgrid', array($lowerLeftLatitude, $lowerLeftLongitude, $upperRightLatitude, $upperRightLongitude, $resolution),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns a list of latitude and longitude pairs along a line defined by two points.  Supports
   * latitudes and longitudes for the Continental United States, Alaska, Hawaii, Guam, and
   * Puerto Rico only.  Provides points in a format suitable for use in calling multi-point
   * functions NDFDgenLatLonList and NDFDgenByDayLatLonList.
   *
   * @param decimal $endPoint1Lat
   * @param decimal $endPoint1Lon
   * @param decimal $endPoint2Lat
   * @param decimal $endPoint2Lon
   * @return string
   */
  public function LatLonListLine($endPoint1Lat, $endPoint1Lon, $endPoint2Lat, $endPoint2Lon) 
    {
    return $this->__soapCall('LatLonListLine', array($endPoint1Lat, $endPoint1Lon, $endPoint2Lat, $endPoint2Lon),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns the latitude and longitude pairs corresponding to a list of one or more zip codes.
   *  Supports zip codes for the Continental United States, Alaska, Hawaii, and Puerto Rico
   * only. Provides points in a format suitable for use in calling multi-point functions NDFDgenLatLonList
   * and NDFDgenByDayLatLonList.
   *
   * @param string $zipCodeList
   * @return string
   */
  public function LatLonListZipCode($zipCodeList) 
    {
    return $this->__soapCall('LatLonListZipCode', array($zipCodeList),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns the latitude and longitude pairs corresponding to a predefined list of US cities.
   *  Provides points in a format suitable for use in calling multi-point functions NDFDgenLatLonList
   * and NDFDgenByDayLatLonList.  The response also includes a list of city names with the
   * order of the names matching the order of the corresponding point.
   *
   * @param integer $displayLevel
   * @return string
   */
  public function LatLonListCityNames(integer $displayLevel) 
    {
    return $this->__soapCall('LatLonListCityNames', array($displayLevel),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns a list of latitude and longitude pairs in a rectangle defined by a central point
   * and distance from that point in the latitudinal and longitudinal directions.  Supports
   * latitudes and longitudes for the Continental United States, Alaska, Hawaii, Guam, and
   * Puerto Rico only.  Provides points in a format suitable for use in calling multi-point
   * functions NDFDgenLatLonList and NDFDgenByDayLatLonList.
   *
   * @param decimal $centerPointLat
   * @param decimal $centerPointLon
   * @param decimal $distanceLat
   * @param decimal $distanceLon
   * @param decimal $resolution
   * @return string
   */
  public function LatLonListSquare($centerPointLat, $centerPointLon, $distanceLat, $distanceLon, $resolution) 
    {
    return $this->__soapCall('LatLonListSquare', array($centerPointLat, $centerPointLon, $distanceLat, $distanceLon, $resolution),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns latitude and longitude pairs of the four corners of an NDFD grid.  Provides points
   * in a format suitable for use in calling multi-point functions NDFDgenLatLonList and NDFDgenByDayLatLonList.
   *  Supports latitudes and longitudes for the Continental United States, Hawaii, Guam, and
   * Puerto Rico only.  Also provides a minimum resolution for requesting the grid.
   *
   * @param string $sector
   * @return string
   */
  public function CornerPoints($sector) 
    {
    return $this->__soapCall('CornerPoints', array($sector),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns National Weather Service digital weather forecast data encoded in GML.  Supports
   * latitudes and longitudes for the Continental United States, Alaska, Hawaii, Guam, and
   * Puerto Rico only.  The latitude and longitude are delimited by a comma and multiple pairs
   * are delimited by a space (i.e. 30.00,-77.00 40.00,-90.00). Allowable values for the input
   * variable "featureType" are "Forecast_Gml2Point", "Forecast_GmlObs", "NdfdMultiPointCoverage",
   * "Ndfd_KmlPoint", and "Forecast_GmlsfPoint".  For all feature types a time (UTC) is required
   * to indicate when data is requested.  The input variable "weatherParameters" has array
   * elements set to "true" to indicate which weather parameters are being requested.  If an
   * array element is set to "false", data for that weather parameter are not to be returned.
   *
   *
   * @param string $listLatLon
   * @param dateTime $requestedTime
   * @param string $featureType
   * @param weatherParametersType $weatherParameters
   * @return string
   */
  public function GmlLatLonList($listLatLon, $requestedTime, $featureType, weatherParametersType $weatherParameters) 
    {
    return $this->__soapCall('GmlLatLonList', array($listLatLon, $requestedTime, $featureType, $weatherParameters),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns National Weather Service digital weather forecast data encoded in GML.  Supports
   * latitudes and longitudes for the Continental United States, Alaska, Hawaii, Guam, and
   * Puerto Rico only.  The latitude and longitude are delimited by a comma and multiple pairs
   * are delimited by a space (i.e. 30.00,-77.00 40.00,-90.00). Allowable values for the input
   * variable "featureType" are "Forecast_Gml2Point", "Forecast_GmlObs", "NdfdMultiPointCoverage",
   * "Ndfd_KmlPoint", and "Forecast_GmlsfPoint".  For all feature types a start and end time
   * (UTC) is required to indicate when data is requested.  a comparison type (IsEqual, Between,
   * GreaterThan, GreaterThan, GreaterThanEqualTo, LessThan, and  LessThanEqualTo). The input
   * variable "propertyName" contains a comma delimited string of NDFD element to indicate
   * which weather parameters are being requested.
   *
   * @param string $listLatLon
   * @param dateTime $startTime
   * @param dateTime $endTime
   * @param string $compType
   * @param string $featureType
   * @param string $propertyName
   * @return string
   */
  public function GmlTimeSeries($listLatLon, $startTime, $endTime, $compType, $featureType, $propertyName) 
    {
    return $this->__soapCall('GmlTimeSeries', array($listLatLon, $startTime, $endTime, $compType, $featureType, $propertyName),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns National Weather Service digital weather forecast data.  Supports latitudes and
   * longitudes for the Continental United States, Hawaii, Guam, and Puerto Rico only.  Allowable
   * values for the input variable "format" are "24 hourly" and "12 hourly".  The input variable
   * "startDate" is a date string representing the first day (Local) of data to be returned.
   * The input variable "numDays" is the integer number of days for which the user wants data.
   *
   *
   * @param decimal $latitude
   * @param decimal $longitude
   * @param date $startDate
   * @param integer $numDays
   * @param string $format
   * @return string
   */
  public function NDFDgenByDay($latitude, $longitude, date $startDate, integer $numDays, $format) 
    {
    return $this->__soapCall('NDFDgenByDay', array($latitude, $longitude, $startDate, $numDays, $format),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }

  /**
   * Returns National Weather Service digital weather forecast data.  Supports latitudes and
   * longitudes for the Continental United States, Hawaii, Guam, and Puerto Rico only.  The
   * latitude and longitude are delimited by a comma and multiple pairs are delimited by a
   * space (i.e. 30.00,-77.00 40.00,-90.00). Allowable values for the input variable "format"
   * are "24 hourly" and "12 hourly".  The input variable "startDate" is a date string representing
   * the first day (Local) of data to be returned. The input variable "numDays" is the integer
   * number of days for which the user wants data.
   *
   * @param string $listLatLon
   * @param date $startDate
   * @param integer $numDays
   * @param string $format
   * @return string
   */
  public function NDFDgenByDayLatLonList($listLatLon, date $startDate, integer $numDays, $format) 
    {
    return $this->__soapCall('NDFDgenByDayLatLonList', array($listLatLon, $startDate, $numDays, $format),       array(
            'uri' => 'http://www.weather.gov/forecasts/xml/DWMLgen/wsdl/ndfdXML.wsdl',
            'soapaction' => ''
           )
      );
  }
};
?>