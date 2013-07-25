<?php
/**
 * Set of classes to programatically create certain aspects of the
 * approved brand at NC State University
 *
 * @package Ncstate
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Utility class to keep up with the version of the PHP API
 *
 */
final class Ncstate_Version
{
    /**
     * Version of the API
     * @var string
     */
    const VERSION = '1.0.9';

    /**
     * Compare the specified version string $version with the current
     * Ncstate_Version::VERSION
     *
     * Borrowed from Zend_Version implementation in Zend Framework
     *
     * @param string $version - A version string (e.g. "0.7.1").
     * @return int -1 if the $version is older,
     *             0 if they are the same,
     *             and +1 if $version is newer.
     *
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }
}
