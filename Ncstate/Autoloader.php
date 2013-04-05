<?php
/**
 * Set of classes to programatically create certain aspects of the
 * approved brand at NC State University
 *
 * @package Ncstate
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * This automatically loads the classes whenever they are needed.
 */
class Ncstate_Autoloader
{
    /**
     * Registers this autoloader on the SPL autoloader stack. Once you do
     * this, you can use any class in the Ncstate_ hierarchy directly.
     */
    public static function register()
    {
        spl_autoload_register(array('Ncstate_Autoloader', 'loadClass'));
    }

    /**
     * Finds the file that the given class would live in, and requires it.
     *
     * @param string $class The name of a class.
     */
    public static function loadClass($class)
    {
        if (strpos($class, 'Ncstate_') !== 0) {
            return;
        }

        $filename = rtrim(dirname(dirname(__FILE__)), "/\\") . '/' .
                    str_replace('_', '/', $class) . '.php';

        if (is_file($filename)) {
            require $filename;
        }
    }
}