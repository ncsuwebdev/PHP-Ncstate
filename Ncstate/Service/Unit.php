<?php
/**
 * Set of classes to programatically communicate with services at NC State
 * University
 *
 * @package Ncstate_Service
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Provides an interface to the building tables in NC State's LDAP services to
 * provide information about buildings associated with NC State.
 *
 * @see http://www.ldap.ncsu.edu/datadetails.php#buildings
 */
class Ncstate_Service_Unit extends Ncstate_Service_Ldap_Connector
{
    /**
     * Context for all NC State buildings
     *
     * @var string
     */
    const UNIT_CONTEXT = 'ou=units,dc=ncsu,dc=edu';

    public function getUnits($returnFields = array())
    {
        $units = $this->search("ou=*", self::UNIT_CONTEXT, $returnFields);

        usort($units, array($this, '_sort'));

        return $units;
    }

    protected function _sort($a, $b)
    {
        return strcmp($a['description'], $b['description']);
    }
}