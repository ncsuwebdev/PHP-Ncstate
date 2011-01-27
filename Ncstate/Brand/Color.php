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