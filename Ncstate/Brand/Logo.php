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
 * Include the branded color class
 */
require_once 'Ncstate/Brand/Color.php';

/**
 * Service class to reliably generate on-brand logos that typically
 * live at the top of web pages at NC State University.
 * 
 * Logos have 2 distinct textual representations, where the first
 * part of the text is bold and the second is normal weight.
 * 
 * The class has the ability to use a public service provided by OIT
 * to create the image logo using the correct Universe font.  The font
 * is licensed per user and not distributed with this release, although
 * all the methodology is present in this class to use other fonts.
 * 
 */
class Ncstate_Brand_Logo
{   
    /**
     * First part of the textual logo which is bold
     * @var string
     */
    protected $_boldText = 'NC STATE ';
    
    /**
     * Second part of the textual logo which is normal weight
     * @var string
     */
    protected $_normalText = 'UNIVERSITY';
    
    /**
     * URL to the public service to generate the images
     * @var string
     */
    const GENERATOR_SERVICE_URL = 'http://webapps.ncsu.edu/logoapi/';
    
    /**
     * Available configuration options for generating the logo
     * 
     * @var array
     */
    protected $_options = array(
        'width'           => '470',            // width of the image
        'height'          => '60',             // height of the image
        'leftTextOffset'  => '10',             // distance from left border to indent the text
        'backgroundColor' => 'primary-red',    // brand-approved key for background color of the image
        'fontSize'        => '36',             // font size (in points) of the text
        'fontColor'       => 'primary-white',  // brand-approved font color for the text
        'verticalAlign'   => 'center',         // position vertically within the image height of the text
        'transparent'     => false,            // boolean operation for making the background transparent
        'pathToFonts'     => '',               // absolute path to the font files which 
        'normalFont'      => 'UVC_____.ttf',   // font used to write the normal weight text
        'boldFont'        => 'UVCB____.ttf',   // font used to write the bold text
        'imageType'       => 'png',            // image format to save the image, can be png, gif, or jpeg
        'savePath'        => null,             // path to save the image to
    );
    
    /**
     * Constructor
     * @param string $boldText - bold text
     * @param string $normalText - normal weight text
     * @param array $options - array of options
     */
    public function __construct(
        $boldText = null,
        $normalText = null, 
        array $options = array())
    {
        if (!is_null($boldText)) {
            $this->setBoldText($boldText);
        }
        
        if (!is_null($normalText)) {
            $this->setNormalText($normalText);
        }
        
        $this->setOptions($options);
    }
    
    /**
     * Sets a value for the bold text
     * 
     * @param string $text
     * @return object for fluent interface
     */
    public function setBoldText($text)
    {
        $this->_boldText = strtoupper($text);
        
        return $this;
    }
    
    /**
     * Gets the value for the bold text
     * 
     * @return string
     */
    public function getBoldText()
    {
        return $this->_boldText;
    }
    
    /**
     * Sets a value for the normal weight text
     * 
     * @param string $text
     * @return object for fluent interface
     */
    public function setNormalText($text)
    {
        $this->_normalText = strtoupper($text);
        
        return $this;
    }

    /**
     * Gets the value for the normal weight text
     * 
     * @return string
     */
    public function getNormalText()
    {
        return $this->_normalText;
    }
    
    /**
     * Sets options as outlined in the class variable
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
     * Gets the options array
     * 
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
    /**
     * Gets the image from the remote service based on the 
     * options set in the object.
     * 
     * @throws Ncstate_Brand_Exception
     * @return image resource
     */
    public function getImage()
    {
        // merge text options with generation options
        $queryArgs = array_merge(
            $this->getOptions(),
            array(
            	'normalText' => $this->getNormalText(),
            	'boldText'   => $this->getBoldText(),
            )
        );
        
        $url = self::GENERATOR_SERVICE_URL . '?' . http_build_query($queryArgs);
        
        // Set curl options and execute the request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 

        $result = curl_exec($ch);
        
        // if the request fails, throw exception
        if ($result === false) {
            require_once 'Ncstate/Brand/Exception.php';
            throw new Ncstate_Brand_Exception('CURL Error: ' . curl_error($ch));
        }
        
        $im = imagecreatefromstring($result);
        curl_close($ch);    
        
        return $this->_store($im);
    }
    
    /**
     * Local method to create the image yourself.  Does not use the service
     * to generate the image, but uses PHP's GD library.
     * 
     * @throws Ncstate_Brand_Exception
     * @return Image resource
     */
    public function createImage()
    {
        // set up base image, and colors to be used in the image (black not used for this page, but left for reference later)
        $im = imagecreatetruecolor($this->_options['width'], $this->_options['height']);
        
        $brandColor = new Ncstate_Brand_Color();
        
        // Get the background color on brand
        $backgroundColorRgb = $brandColor->getColor($this->_options['backgroundColor'], true);
        if (is_null($backgroundColorRgb)) {
            require_once 'Ncstate/Brand/Exception.php';
            throw new Ncstate_Brand_Exception('Proper branding color not found for backgroundColor');
        }
        $backgroundColor = imagecolorallocate($im, $backgroundColorRgb['red'], $backgroundColorRgb['green'], $backgroundColorRgb['blue']);
        
        
        // get the font color on brand
        $fontColorRgb = $brandColor->getColor($this->_options['fontColor'], true);
        if (is_null($fontColorRgb)) {
            require_once 'Ncstate/Brand/Exception.php';
            throw new Ncstate_Brand_Exception('Propoer branding color not found for fontColor');
        }
        $fontColor = imagecolorallocate($im, $fontColorRgb['red'], $fontColorRgb['green'], $fontColorRgb['blue']);
       
        
        // Set the background to be red (the base color of the header region where this will be placed)
        imagefilledrectangle($im, 0, 0, $this->_options['width'], $this->_options['height'], $backgroundColor);
        
        // Path to our font files
        $normalFont = $this->_options['pathToFonts'] . '/' . $this->_options['normalFont'];
        $boldFont = $this->_options['pathToFonts'] . '/' . $this->_options['boldFont'];
        
        // First we create our bounding box for the first text
        $boldBox = imagettfbbox($this->_options['fontSize'], 0, $boldFont, $this->getBoldText());
        
        $boxHeight = $boldBox[1] - $boldBox[7];
        $verticalPosition = 0;
        
        // figure out vertical positioning based on the bounding box and image height
        switch ($this->_options['verticalAlign']) {
            case 'top':
                $verticalPosition = $boxHeight;
                break;
            case 'center':
                $verticalPosition = ceil(($this->_options['height'] + $boxHeight) / 2); 
                break;
            case 'bottom':
            default:
                $verticalPosition = $this->_options['height'];
                break;
        }        
        
        // This is our cordinates for X and Y for the bold text
        $boldX = $boldBox[0] + $this->_options['leftTextOffset']; // bottom left corner of the box
        
        // Write it
        imagettftext($im, $this->_options['fontSize'], 0, $boldX, $verticalPosition, $fontColor, $boldFont, $this->getBoldText());
        
        // Create the next bounding box for the second text
        $regbox = imagettfbbox($this->_options['fontSize'], 0, $normalFont, $this->getNormalText());
        
        // Set the cordinates so its next to the first text
        $normalX = $boldBox[4] + $this->_options['leftTextOffset']; // top right of bold text
        
        // Write it
        imagettftext($im, $this->_options['fontSize'], 0, $normalX, $verticalPosition, $fontColor, $normalFont, $this->getNormalText());
        
        // uncomment this line to make the image have a transparent background (it leaves some artifacts around the text, so it might be easier to just set the background color like we did with $red)
        if ((bool)$this->_options['transparent']) {
            imagecolortransparent ($im, $backgroundColor);
        }
        
        return $this->_store($im);
    }
    
    /**
     * Decides the storage protocol for the image
     * 
     * @param Image resource $im
     * @return Image resource
     */
    protected function _store($im)
    {
        if (!is_null($this->_options['savePath'])) {
            switch ($this->_options['imageType']) {
                case 'gif':
                    imagegif($im, $this->_options['savePath']);
                    break;
                case 'jpeg':
                    imagejpeg($im, $this->_options['savePath']);
                    break;
                case 'png':
                default:
                    imagepng($im, $this->_options['savePath']);
            }
        }
        
        return $im;        
    }
}