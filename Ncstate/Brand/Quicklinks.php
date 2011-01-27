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
 * Class that contains the common quicklinks found in most NC State
 * templates.
 *
 */
final class Ncstate_Brand_Quicklinks
{
    /**
     * Key => Value pairs of links
     * 
     * @var array
     */
    protected $_links = array(
        'Academic Calendar'               => 'http://www.ncsu.edu/registrar/calendars/',
        'Bookstore'                       => 'http://www.fis.ncsu.edu/ncsubookstores/',
        'Campus Administration'           => 'http://www.ncsu.edu/about-nc-state/university-administration/',
        'Cashier\'s Office'               => 'http://www.fis.ncsu.edu/cashier/',
        'Centennial Campus'               => 'http://www.ncsu.edu/about-nc-state/centennial-campus/',
        'Colleges & Academic Departments' => 'http://www.ncsu.edu/academics/index.html',
        'Distance Education'              => 'http://distance.ncsu.edu/',
        'Financial Aid & Scholarships'    => 'http://www7.acs.ncsu.edu/financial_aid/',
        'Graduate School'                 => 'http://www2.acs.ncsu.edu/grad/',
        'Housing'                         => 'http://www.ncsu.edu/campus-life/housing/',
        'Registration & Records'          => 'http://www.ncsu.edu/registrar/',
        'Undergraduate Admissions'        => 'http://www.fis.ncsu.edu/uga/',
        'Vista Courses'                   => 'http://vista.ncsu.edu/',
        'Webmail'                         => 'https://webmail.ncsu.edu/',
        'Wolfware Courses'                => 'http://courses.ncsu.edu/',
    );
    
    /**
     * Gets all the links we know about
     * 
     * @return array
     */
    public function getLinks()
    {
        return $this->_links;
    }
}