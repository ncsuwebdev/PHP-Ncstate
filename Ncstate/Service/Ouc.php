<?php
/**
 * Set of classes to programatically communicate with services at NC State
 * University
 *
 * @package Ncstate_Service
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Service class to wrap the functionality of the NC State OUC API in a
 * simple to use class.
 *
 * The class uses the public OUC API service provided by OIT
 * to get the departmental OUC numbers for campus.
 *
 * @see     http://webapps.ncsu.edu/ouc
 *
 */
class Ncstate_Service_Ouc
{

    /**
     * URL to the public service
     * @var string
     */
    const BASE_URL = 'http://webapps.ncsu.edu/ouc/';

    /**
     * Default response format
     *
     * @var string
     */
    protected $_format = 'json';

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

    public function getOuc($ouc)
    {
        $args = array(
            'ouc' => $ouc
        );

        return $this->_request('getOuc', $args);
    }

    public function getAll($order = 'ouc')
    {
        $args = array(
            'order' => $order
        );

        return $this->_request('getAll', $args);
    }

    public function search($term)
    {
        $args = array(
            'term' => $term
        );

        return $this->_request('searchOuc', $args);
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