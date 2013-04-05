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
 * Service class to reliably generate on-brand text-based images that typically
 * live at the top of web pages at NC State University.
 *
 * The class has the ability to use a public service provided by OIT
 * to create the image using the correct Universe font.  The font
 * is licensed per user and not distributed with this release, although
 * all the methodology is present in this class to use other fonts.
 *
 */
class Ncstate_Brand_Text
{
    /**
     * Default text
     * @var string
     */
    protected $_text = '*TEXT* UTILITY';

    /**
     * URL to the public service to generate the images
     * @var string
     */
    const GENERATOR_SERVICE_URL = 'http://webapps.ncsu.edu/textapi/';

    /**
     * Factor at which to oversample the image.  This is for
     * kerning issues found with dealing with smaller font sizes
     * and restricted image sizes.
     *
     * @var integer
     */
    const OVERSAMPLE_FACTOR = 5;

    /**
     * Available configuration options for generating the image
     *
     * @var array
     */
    protected $_options = array(
        'width'              => '275',            // width of the image
        'height'             => '50',             // height of the image
        'leftTextOffset'     => '8',              // distance from left border to place the text
        'baselineTextOffset' => '8',              // distance from bottom border to place the text
        'lineSpacing'        => '10',             // Spacing between multiple lines, if applicable
        'backgroundColor'    => 'primary-red',    // brand-approved key for background color of the image
        'fontSize'           => '36',             // font size (in points) of the text
        'fontColor'          => 'primary-white',  // brand-approved font color for the text
        'transparent'        => false,            // boolean operation for making the background transparent
        'pathToFonts'        => '',               // absolute path to the font files which
        'normalFont'         => 'UVC_____.TTF',   // font used to write the normal weight text (Univers Condensed 57)
        'boldFont'           => 'UVCB____.TTF',   // font used to write the bold text (Univers Bold Condensed 67)
        'imageType'          => 'png',            // image format to save the image, can be png, gif, or jpeg
        'savePath'           => null,             // path to save the image to
    );

    /**
     * Constructor
     * @param string $boldText - bold text
     * @param string $normalText - normal weight text
     * @param array $options - array of options
     */
    public function __construct(
        $text = null,
        array $options = array())
    {
        if (!is_null($text)) {
            $this->setText($text);
        }

        $this->setOptions($options);
    }

    /**
     * Sets a value for the text
     *
     * @param string $text
     * @return object for fluent interface
     */
    public function setText($text)
    {
        $this->_text = $text;

        return $this;
    }

    /**
     * Gets the value for the text
     *
     * @return string
     */
    public function getText()
    {
        return $this->_text;
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
            	'text' => $this->getText(),
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
        $im = imagecreatetruecolor($this->_options['width'] * self::OVERSAMPLE_FACTOR, $this->_options['height'] * self::OVERSAMPLE_FACTOR);

        $brandColor = new Ncstate_Brand_Color();

        // Get the background color on brand
        $backgroundColorRgb = $brandColor->getColor($this->_options['backgroundColor'], true);
        if (is_null($backgroundColorRgb)) {
            throw new Ncstate_Brand_Exception('Proper branding color not found for backgroundColor');
        }
        $backgroundColor = imagecolorallocate($im, $backgroundColorRgb['red'], $backgroundColorRgb['green'], $backgroundColorRgb['blue']);


        // get the font color on brand
        $fontColorRgb = $brandColor->getColor($this->_options['fontColor'], true);
        if (is_null($fontColorRgb)) {
            throw new Ncstate_Brand_Exception('Propoer branding color not found for fontColor');
        }
        $fontColor = imagecolorallocate($im, $fontColorRgb['red'], $fontColorRgb['green'], $fontColorRgb['blue']);


        // Set the background to be red (the base color of the header region where this will be placed)
        imagefilledrectangle($im, 0, 0, $this->_options['width'] * self::OVERSAMPLE_FACTOR, $this->_options['height'] * self::OVERSAMPLE_FACTOR, $backgroundColor);

        // Path to our font files
        $normalFont = $this->_options['pathToFonts'] . '/' . $this->_options['normalFont'];
        $boldFont = $this->_options['pathToFonts'] . '/' . $this->_options['boldFont'];

        // Calculate a line height for multi-line images
        $oneLine = imagettfbbox($this->_options['fontSize'] * self::OVERSAMPLE_FACTOR, 0, $normalFont, 'A');
        $oneLineHeight = $oneLine[1] - $oneLine[7];

        $posX = $this->_options['leftTextOffset'] * self::OVERSAMPLE_FACTOR;
        $posY = ($this->_options['height'] * self::OVERSAMPLE_FACTOR) - ($this->_options['baselineTextOffset'] * self::OVERSAMPLE_FACTOR);

        $lines = array();

        // Parse the string by newline chars
        $parsed = explode("\n", $this->getText());
        foreach ($parsed as $p) {
            if ($p == '') {
                continue;
            }

            // split up bold parts versus non-bold parts
            $parts = preg_split('/(\*[^*]*\*)|(\n)/', $p, -1, PREG_SPLIT_DELIM_CAPTURE);

            $lines[] = $parts;
        }

        // Reverse the lines so that we write them the correct offset from the baseline
        $lines = array_reverse($lines);

        foreach ($lines as $line) {
            foreach ($line as $text) {
                $count = 0;
                $font = $normalFont;

                // Only make the font bold if there are two bold markers on the particle  of text
                $writable = str_replace('*', '', $text, $count);
                if ($count == 2) {
                    $font = $boldFont;
                }

                $boundingBox = imagettftext($im, $this->_options['fontSize'] * self::OVERSAMPLE_FACTOR, 0, $posX, $posY, $fontColor, $font, $writable);

                $posX = $boundingBox[2];
            }

            $posX = $this->_options['leftTextOffset'] * self::OVERSAMPLE_FACTOR;
            $posY -= ($oneLineHeight + ($this->_options['lineSpacing'] * self::OVERSAMPLE_FACTOR));
        }

        // Sample down the image to the original requested size
        $final = imageCreateTrueColor($this->_options['width'], $this->_options['height']);

        imageCopyResampled($final, $im, 0, 0, 0, 0, $this->_options['width'], $this->_options['height'], $this->_options['width'] * self::OVERSAMPLE_FACTOR, $this->_options['height'] * self::OVERSAMPLE_FACTOR );

        // Option to make the image transparent
        if ((bool)$this->_options['transparent']) {
            imagecolortransparent($final, $backgroundColor);
        }

        return $this->_store($final);
    }


    /**
     * Checks to see if the text to be placed on the image is
     * not "NC STATE UNIVERSITY" which should instead be used
     * as the official brick logo provided at
     * http://ncsu.edu/brand
     *
     * @param string $text - Text to check
     * @return boolean
     */
    public function isValidText($text)
    {
        $invalid = array(
            '/NC\s*STATE\s*UNIVERSITY/i'
        );

        $checkText = str_replace('*', '', $text);
        foreach ($invalid as $i) {
            if (preg_match($i, $checkText)) {
                return false;
            }
        }

        return true;
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