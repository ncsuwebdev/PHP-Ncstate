<?php
/**
 * Set of classes to programatically create certain aspects of the
 * approved brand at NC State University
 * 
 * @package Ncstate_Brand 
 * @see     http://ncsu.edu/brand
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Class to generate the HTML code for the NC State Branding bar which is
 * required on all NC State public web pages.
 * 
 */
final class Ncstate_Brand_Bar
{
    /**
     * URL to the style sheet
     * 
     * @var string
     */
    const STYLESHEET_URL = 'http://www.ncsu.edu/brand/utility-bar/iframe/css/utility_bar_iframe.css';
    
    /**
     * 
     * Base URL for the Iframe which is injected into the top of the web page
     * 
     * @var string
     */
    const IFRAME_URL = 'http://www.ncsu.edu/brand/utility-bar/iframe/index.php';
    
    /**
     * Options available for configuring the brand bar.
     * 
     * @var array
     */
    protected $_options = array(
        'siteUrl'        => '',     // URL for the website the bar will live on.  Used in google site search.
        'color'          => 'red',  // Option for color combo.  Can be red, black, red_on_white, or black_on_white
        'centered'       => true,   // Boolean value for centering the bar or not
        'noIframePrompt' => 'Your browser does not support inline frames or is currently configured  not to display inline frames.<br /> Visit <a href="http://ncsu.edu/">http://www.ncsu.edu</a>.',  // Prompt provided to users who have browsers that don't support iframes
        'iframeId'       => 'ncsu_branding_bar',  // HTML id for the branding bar iframe
    );
    
    /**
     * Available color options for the branding bar
     * 
     * @var array
     */
    protected $_colorOptions = array(
        'red',
        'black',
        'red_on_white',
        'black_on_white',
    );
    
    /**
     * Constructor
     * 
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }
    
    /**
     * Sets the options for the branding bar
     * 
     * @param array $options
     * @return object for fluent interface
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (isset($this->_options[$key])) {
                $this->_options[$key] = $value;
            }
        }
        
        return $this;
    }
    
    /**
     * Gets the options for the branding bar
     */
    public function getOptions()
    {
        return $this->_options;
    }    
    
    /**
     * Gets the full HTML of the branding bar, including stylesheet and iframe code
     * 
     * @return string
     */
    public function getBarHtml()
    {
        return "\n" . $this->getStylesheetHtml() . "\n" . $this->getIframeHtml() . "\n";
    }
    
    /**
     * Gets the HTML for the style sheet
     * 
     * @return string
     */
    public function getStylesheetHtml()
    {
        return '<link rel="stylesheet" type="text/css" href="' . $this->getStylesheetUrl() . '" media="screen" />';
    }
    
    /**
     * Gets the URL for the style sheet
     * 
     * @return string
     */
    public function getStylesheetUrl()
    {
        return self::STYLESHEET_URL;
    }
    
    /**
     * Gets the HTML for the iframe as set by the options
     * 
     * @return string
     */
    public function getIframeHtml()
    {
        return '<iframe name="' . $this->_options['iframeId'] . '" id="' 
             . $this->_options['iframeId'] . '" frameborder="0" src="' . $this->getIframeUrl() . '" scrolling="no">'
             . $this->_options['noIframePrompt']
             . '</iframe>';
    }
    
    /**
     * Gets the configured URL for the branding bar that the iframe occupies
     * 
     * @return string
     */
    public function getIframeUrl()
    {
        if (!in_array($this->_options['color'], $this->_colorOptions)) {
            $this->_options['color'] = 'red';
        }
        
        if (preg_match('/^http(s)*:\/\//i', $this->_options['siteUrl'])) {
            $this->_options['siteUrl'] = preg_replace('/^http(s)*:\/\//i', '', $this->_options['siteUrl']);
        }
        
        return self::IFRAME_URL . '?color=' . urlencode($this->_options['color']) 
             . '&inurl=' . urlencode($this->_options['siteUrl']) . '&center=' 
             . urlencode((($this->_options['centered']) ? 'yes' : 'no'));
    }
}