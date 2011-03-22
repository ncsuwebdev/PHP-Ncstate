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
 * Utility class to group together official brand colors for use
 * in other utility classes.
 *
 */
final class Ncstate_Brand_Color
{
    /**
     * Ratio to determine pass or not for WCAG 2 AA Large Text
     * 
     * @var float
     */
    const WCAG_LARGETEXT_RATIO_AA = 3;
    
    /**
     * Ratio to determine pass or not for WCAG 2 AA Normal Text
     * 
     * @var float
     */    
    const WCAG_NORMALTEXT_RATIO_AA = 4.5;
    
    /**
     * Ratio to determine pass or not for WCAG 2 AAA Large Text
     * 
     * @var float
     */    
    const WCAG_LARGETEXT_RATIO_AAA = 4.5;

    /**
     * Ratio to determine pass or not for WCAG 2 AAA Normal Text
     * 
     * @var float
     */    
    const WCAG_NORMALTEXT_RATIO_AAA = 7;
    
    /**
     * Brand colors separated by style
     * 
     * @var array
     */
    protected $_colors = array(
        'primary' => array(
            'red'   => 'CC0000',
            'black' => '000000',
            'white' => 'FFFFFF'
        ),
        'secondary' => array(
            'grey1'  => '383838',
            'grey2'  => '666666',
            'grey3'  => 'CCCCCC',
            'grey4'  => 'E1E1E1',
            'green1' => '5C5541',
            'green2' => '666633',
            'blue'   => '556677',
            'red'    => 'A20000',
        ),
        'support' => array(
            'brown1' => 'A79574',
            'brown2' => 'C5BD9D',
            'brown3' => 'E5E1D0',
            'green1' => '778855',
            'green2' => '99AA77',
            'green3' => 'CCDDAA',
            'blue'   => '67849C',
            'yellow' => 'CC9900',
        ),
    );
    
    /**
     * List of color keys that are valid for header
     * font colors
     * 
     * @var array
     */    
    protected $_validHeaderFontColors = array(
        'primary-red',
        'primary-black',
        'primary-white',
    );
    
    /**
     * List of color keys that are valid for header
     * background colors
     * 
     * @var array
     */
    protected $_validHeaderBackgroundColors = array(
        'primary-red',
        'primary-black',
        'primary-white',
    );    
    
    
    /**
     * Gets a specific brand color by it's key.
     * 
     * EX:  $this->getColor('primary-red') would return '#CC0000';
     * 
     * @param string $color - brand key
     * @param boolean $rgb - whether or not to return the RGB instead of the hex value
     */
    public function getColor($color, $rgb = false)
    {
        $color = explode('-', $color);
        
        if (!isset($this->_colors[$color[0]][$color[1]])) {
            return null;
        } 
        
        return ($rgb) ? $this->_asRGB($this->_colors[$color[0]][$color[1]]) : '#' . $this->_colors[$color[0]][$color[1]];
    }
    
    
    /**
     * Gets all the colors in a flattened array where the key would be primary-red.
     * 
     * Can also return a specific color level such as "primary"
     * 
     * @param string $level - level key
     * @param boolean $rgb - whether or not to return the RGB instead of the hext value
     */
    public function getColors($level = null, $rgb = false)
    {
        if (!is_null($level)) {
            return (isset($this->_colors[$level])) ? $this->_colors[$level] : null;
        }
        
        $colorList = array();
        foreach ($this->_colors as $level => $list) {
            foreach ($list as $name => $hex) {
                $colorList[$level . '-' . $name] = ($rgb) ? $this->_asRGB($hex) : $hex;
            }
        }
        
        return $colorList;
    }
    
    
    /**
     * Checks if a combination of font and background colors are valid based
     * on guidelines provided by NC State University.
     * 
     * http://www.ncsu.edu/brand
     * 
     * @param string $fontColor - Color key as defined in this class
     * @param string $backgroundColor - Color key as defined in this class
     * @return boolean
     */
    public function isValidWebsiteHeader($fontColor, $backgroundColor) 
    {
        if (!in_array($fontColor, $this->_validHeaderFontColors)) {
            return false;
        }
        
        if (!in_array($backgroundColor, $this->_validHeaderBackgroundColors)) {
            return false;
        }
        
        if ($fontColor == $backgroundColor) {
            return false;
        }
        
        return true;
    }
    
    
    /**
     * Evaluates 2 on-brand colors based on the WCAG 2 requirements for color
     * contrast found at:
     * 
     * http://www.w3.org/TR/WCAG20/#visual-audio-contrast (1.4.3)
     * http://www.w3.org/TR/WCAG20/#larger-scaledef
     * 
     * Font size and boldness affect passability.
     * 
     * @param string $colorKey1 - color key (as defined in this class) to compare
     * @param string $colorKey2 - color key (as defined in this class) to compare
     * @param int $fontSize - font size in points
     * @param boolean $isBold - whether or not the text is bold
     * @param AA|AAA $level - WCAG level to evaluate
     * @throws Ncstate_Brand_Exception
     * @return boolean
     */
    public function isValidColorContrast($colorKey1, $colorKey2, $fontSize, $isBold = false, $level = 'AA')
    {
        $color1luminosity = $this->_calculateLuminosity($colorKey1);
        $color2luminosity = $this->_calculateLuminosity($colorKey2);
        
        $ratio = 0;
        
        // Get luminosity ratio
        if ($color1luminosity > $color2luminosity) {
            $ratio = ($color1luminosity + 0.05) / ($color2luminosity + 0.05);
        } else {
            $ratio = ($color2luminosity + 0.05) / ($color1luminosity + 0.05);
        }
        
        // Large text as determined by WCAG2 standards
        $largeText = false;
        if (($isBold && $fontSize >= 14) || $fontSize >= 18) {
            $largeText = true;
        }
        
        // Evaluate based on requested level
        if ($level == 'AA') {
            if ($largeText) {
                return ($ratio >= self::WCAG_LARGETEXT_RATIO_AA);
            }
            
            return ($ratio >= self::WCAG_NORMALTEXT_RATIO_AA);
            
        } elseif ($level == 'AAA') {
            
            if ($largeText) {
                return ($ratio >= self::WCAG_LARGETEXT_RATIO_AAA);
            }
            
            return ($ratio >= self::WCAG_NORMALTEXT_RATIO_AAA);
            
        }
        
        // throw an exception if an invalid level is passed
        require_once 'Ncstate/Brand/Exception.php';
        throw new Ncstate_Brand_Exception('Invalid WCAG level passed');
    }
    
    
    /**
     * Caclulates the luminosity of a given color based on WCAG2 reccomendations
     * 
     * http://www.w3.org/TR/WCAG20/#relativeluminancedef
     * 
     * @param string $colorKey - Color key as defined by this class
     * @throws Ncstate_Brand_Exception
     */
    protected function _calculateLuminosity($colorKey)
    {
        $rgb = $this->getColor($colorKey, true);

        if (is_null($rgb)) {
            require_once 'Ncstate/Brand/Exception.php';
            throw new Ncstate_Brand_Exception('Invalid color code passed as "' . $colorKey . '"');
        }   
        
        $r = $rgb['red'] / 255;
        $g = $rgb['green'] / 255;
        $b = $rgb['blue'] / 255;
        
        if ($r <= 0.03928) {
            $r = $r / 12.92;	
        } else {
            $r = pow((($r + 0.055) / 1.055), 2.4);
        }
        
        if ($g <= 0.03928) {
            $g = $g / 12.92;	
        } else {
            $g = pow((($g + 0.055) / 1.055), 2.4);
        }
        
        if ($b <= 0.03928) {
            $b = $b / 12.92;	
        } else {
            $b = pow((($b + 0.055) / 1.055), 2.4);
        }
        	
        $luminosity = (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
        
        return $luminosity;
    }
    
    
    /**
     * Converts hex value to RGB
     * 
     * @param string $hex
     */
    protected function _asRGB($hex)
    {
        return array(
            'red'   => hexdec(substr($hex, 0, 2)),
            'green' => hexdec(substr($hex, 2, 2)),
            'blue'  => hexdec(substr($hex, 4, 2)),
        );
    }
}