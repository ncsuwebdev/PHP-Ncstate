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
class Ncstate_Service_Building extends Ncstate_Service_Ldap_Connector
{
    /**
     * Context for all NC State buildings
     *
     * @var string
     */
    const BUILDING_CONTEXT = 'ou=buildings,dc=ncsu,dc=edu';

    public function getBuildings($returnFields = array())
    {
        $buildings = $this->search("ncsuBldgAbbrev=*", self::BUILDING_CONTEXT, $returnFields);

        usort($buildings, array($this, '_sort'));

        return $buildings;
    }

    protected function _sort($a, $b)
    {
        return strcmp($a['description'], $b['description']);
    }
}