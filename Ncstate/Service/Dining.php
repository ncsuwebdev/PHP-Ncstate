<?php
/**
 * Set of classes to programatically communicate with services at NC State
 * University
 *
 * @package Ncstate_Service
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Service class to wrap the functionality of the NC State Dining API in a
 * simple to use class.
 *
 * The class uses the public Dining API service provided by OIT
 * to get the menus, locations, and hours for the various dining establishments
 * on campus.
 *
 * @see     http://webapps.ncsu.edu/dining
 *
 */
class Ncstate_Service_Dining
{

    /**
     * URL to the public service to generate the images
     * @var string
     */
    const BASE_URL = 'http://www.ncsudining.com/diningapi/';

    /**
     * The API version to use.  It uses Version 2.0 by default
     *
     * @var integer
     */
    protected $_version = 2;

    /**
     * Default response format
     *
     * @var string
     */
    protected $_format = 'json';

    /**
     * The valid options for the meal types
     *
     * @var array
     */
    protected $_validMealTypes = array('all', 'breakfast', 'lunch', 'brunch', 'dinner');

    /**
     * The valid options for the diet types
     *
     * @var array
     */
    protected $_validDietTypes = array('iron', 'weightGain', 'loseBodyFat', 'calcium', 'vegetarian', 'inactiveDay');

    /**
     * Stores the last parsed response from the server
     *
     * @var stdClass
     */
    protected $_lastParsedResponse = null;

    /**
     * Stores the last raw response from the server
     *
     * @var string
     */
    protected $_lastRawResponse = null;

    /**
     * Stores the last requested URI
     *
     * @var string
     */
    protected $_lastRequestUri = null;


    /**
     * Constructor
     *
     */
    public function __construct()
    {}

    /**
     * Returns the currently set API version to use
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Sets the API version to use.
     *
     * @return int $version Version of the API to use
     */
    public function setVersion($version)
    {
        $this->_version = (int)$version;

        return $this;
    }

    /**
     * Returns the currently set response format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->_format;
    }

    /**
     * Sets the response format.  Must be either 'xml' or 'json'.  Will set
     * to 'json' by default if something invalid is specified.
     *
     * @return string
     */
    public function setFormat($format)
    {

        $format = strtolower($format);

        if ($format != 'xml' && $format != 'json') {
            $this->_format = 'json';
        } else {
            $this->_format = $format;
        }

        return $this;
    }

    /**
     * Returns the menu for a given location key.
     *
     * @param string $locationKey The key for the location for which to get the menu
     * @param string $date Date in YYYY-M-D format or 'today' or 'tomorrow' (today by default)
     * @param string $meal Only return the menu for the specified meal (must be  'breakfast', 'lunch', 'brunch', 'dinner', or 'all')
     * @param string $diet Optionally return the menu items that match a specific
     *                      diet ('iron', 'weightGain', 'loseBodyFat', 'calcium', 'vegetarian', 'inactiveDay')
     *
     * @throws Ncstate_Service_Exception
     *
     * @return stdClass object from the request
     *
     */
    public function getMenu($locationKey, $meal = null, $date = null, $diet = null)
    {

        if (!is_null($meal)) {
            $meal = strtolower($meal);

            if (!in_array($meal, $this->_validMealTypes)) {
                throw new Ncstate_Service_Exception('Meal type must be one of "' . implode(', ', $this->_validMealTypes) . '"');
            }
        }

        if (!is_null($diet)) {

            $diet = strtolower($diet);

            if (!in_array($diet, $this->_validDietTypes)) {
                throw new Ncstate_Service_Exception('Diet type must be one of "' . implode(', ', $this->_validDietTypes) . '"');
            }
        }

        $args = array(
            'location' => $locationKey
        );

        if (!is_null($meal)) {
            $args['meal'] = $meal;
        }

        if (!is_null($date)) {
            $args['date'] = $date;
        }

        if (!is_null($diet)) {
            $args['diet'] = $diet;
        }

        return $this->_request('getMenu', $args);
    }

    /**
     * Returns the hours for a given location key.  Note: A given location may have
     * more than one set of hours.
     *
     * @param string $locationKey The key for the location for which to get the menu
     * @param string $date Date in YYYY-M-D format or 'today' or 'tomorrow' (today by default)
     *
     * @throws Ncstate_Service_Exception
     *
     * @return stdClass object from the request
     *
     */
    public function getHours($locationKey, $date = null)
    {
        $args = array(
            'location' => $locationKey
        );

        if (!is_null($date)) {
            $args['date'] = $date;
        }

        return $this->_request('getHours', $args);
    }

    /**
     * Returns a list of all the locations.  If a location type is speicifed, it
     * will only return locations of that type.
     *
     * @param string $type A valid location type.
     *
     * @throws Ncstate_Service_Exception
     *
     * @return stdClass object from the request
     *
     */
    public function getLocations($type = null)
    {
        $args = array();

        if (!is_null($type)) {
            $args['type'] = $type;
        }

        return $this->_request('getLocations', $args);
    }

    /**
     * Returns a list of all the location types.
     *
     * @throws Ncstate_Service_Exception
     *
     * @return stdClass object from the request
     *
     */
    public function getLocationTypes()
    {
        $args = array();

        return $this->_request('getLocationTypes', $args);
    }

    /**
     * Sends a request using curl to the required endpoint
     *
     * @param string $method The method to call
     * @param array $args key value array of arguments
     *
     * @throws Ncstate_Service_Exception
     *
     * @return stdClass object
     */
    protected function _request($method, $args)
    {
        $this->_lastRequestUri = null;
        $this->_lastResponse = null;
        $this->_lastParsedResponse = null;

        // Add the method we are calling
        $args['method'] = $method;

        // Add the version of the API to use
        $args['v'] = $this->_version;

        // Append the API key to the args passed in the query string
        $args['format'] = $this->_format;

        // Clean up the empty args so they'll return the API's default
        foreach ($args as $key => $value) {
            if ($value == '') {
                unset($args[$key]);
            }
        }

        $this->_lastRequestUri = self::BASE_URL . '?' . http_build_query($args);

        // Set curl options and execute the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_lastRequestUri);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $this->_lastRawResponse = curl_exec($ch);

        if ($this->_lastRawResponse === false) {

            $this->_lastRawResponse = curl_error($ch);
            throw new Ncstate_Service_Exception('CURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        // Response comes back as either JSON or XML, so we decode it into a stdClass object
        if ($args['format'] == 'xml') {
            //@TODO parse XML
            $this->_lastParsedResponse = simplexml_load_string($this->_lastRawResponse);
            $this->_lastParsedResponse = self::_convertSimpleXmlElementObjectIntoArray($this->_lastParsedResponse);
        } else {
            $this->_lastParsedResponse = json_decode($this->_lastRawResponse, true);
        }

        // The response will always have a wrapper element that is the version of the API we're using.
        // We don't care about that, so we'll strip it out
        $this->_lastParsedResponse = array_pop($this->_lastParsedResponse);

        // if it was xml, we'll remove the attributes that were attached to the
        // root element as they were meaningless
        if ($this->_format == 'xml') {
            unset($this->_lastParsedResponse['@attributes']);
        }

        // Server provides error messages in the 'status' field.  It will either be 'success' or 'failure'
        if (strtolower($this->_lastParsedResponse[$method]['status']) == 'failure') {
            throw new Ncstate_Service_Exception('Dining Service Error: ' .
                    $this->_lastParsedResponse['response']['message']);
        }

        return $this->getLastParsedResponse();
    }

    /**
     * Gets the last parsed response from the service
     *
     * @return null|stdClass object
     */
    public function getLastParsedResponse()
    {
        return $this->_lastParsedResponse;
    }

    /**
     * Gets the last raw response from the service
     *
     * @return null|json string
     */
    public function getLastRawResponse()
    {
        return $this->_lastRawResponse;
    }

    /**
     * Gets the last request URI sent to the service
     *
     * @return null|string
     */
    public function getLastRequestUri()
    {
        return $this->_lastRequestUri;
    }

    protected static function _convertSimpleXmlElementObjectIntoArray($simpleXmlElementObject, &$recursionDepth=0) {
      // Keep an eye on how deeply we are involved in recursion.

      // only allow recursion to get to 25 levels before we say nevermind.
      if ($recursionDepth > 25) {
        // Fatal error. Exit now.
        return(null);
      }

      if ($recursionDepth == 0) {
        if (!$simpleXmlElementObject instanceof SimpleXMLElement) {
          // If the external caller doesn't call this function initially
          // with a SimpleXMLElement object, return now.
          return(null);
        } else {
          // Store the original SimpleXmlElementObject sent by the caller.
          // We will need it at the very end when we return from here for good.
          $callerProvidedSimpleXmlElementObject = $simpleXmlElementObject;
        }
      } // End of if ($recursionDepth == 0) {


      if ($simpleXmlElementObject instanceof SimpleXMLElement) {
        // Get a copy of the simpleXmlElementObject
        $copyOfsimpleXmlElementObject = $simpleXmlElementObject;
        // Get the object variables in the SimpleXmlElement object for us to iterate.
        $simpleXmlElementObject = get_object_vars($simpleXmlElementObject);
      }


      // It needs to be an array of object variables.
      if (is_array($simpleXmlElementObject)) {
        // Initialize the result array.
        $resultArray = array();
        // Is the input array size 0? Then, we reached the rare CDATA text if any.
        if (count($simpleXmlElementObject) <= 0) {
          // Let us return the lonely CDATA. It could even be
          // an empty element or just filled with whitespaces.
          return (trim(strval($copyOfsimpleXmlElementObject)));
        }


        // Let us walk through the child elements now.
        foreach($simpleXmlElementObject as $key=>$value) {
          // When this block of code is commented, XML attributes will be
          // added to the result array.
          // Uncomment the following block of code if XML attributes are
          // NOT required to be returned as part of the result array.
          /*
          if((is_string($key)) && ($key == '@attributes')) {
            continue;
          }
          */

          // Let us recursively process the current element we just visited.
          // Increase the recursion depth by one.
          $recursionDepth++;
          $resultArray[$key] =
            self::_convertSimpleXmlElementObjectIntoArray($value, $recursionDepth);


          // Decrease the recursion depth by one.
          $recursionDepth--;
        } // End of foreach($simpleXmlElementObject as $key=>$value) {


        if ($recursionDepth == 0) {
          // That is it. We are heading to the exit now.
          // Set the XML root element name as the root [top-level] key of
          // the associative array that we are going to return to the caller of this
          // recursive function.
          $tempArray = $resultArray;
          $resultArray = array();
          $resultArray[$callerProvidedSimpleXmlElementObject->getName()] = $tempArray;
        }


        return ($resultArray);
      } else {
        // We are now looking at either the XML attribute text or
        // the text between the XML tags.
        return (trim(strval($simpleXmlElementObject)));
      } // End of else
    } // End of function convertSimpleXmlElementObjectIntoArray.
}
