<?php
/**
 * Set of classes to programatically communicate with services at NC State
 * University
 *
 * @package Ncstate_Service
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Generic connector to the NC State LDAP service.  Connects to secure service
 * when a username and password are provided, otherwise it connects to the
 * unsecure service on anonymous bind.
 *
 * @see http://www.ldap.ncsu.edu
 */

class Ncstate_Service_Ldap_Connector
{
    /**
     * NC State's secure LDAP service
     *
     * @var string
     */
    const SECURE_LDAP_SERVER = 'ldaps://ldap.ncsu.edu';

    /**
     * NC State's unsecure LDAP service (for anonymous binds)
     *
     * @var string
     */
    const LDAP_SERVER = 'ldap://ldap.ncsu.edu';

    /**
     * The LDAP link object
     *
     * @var resource
     */
    private $_link = null;

    /**
     * Whether the LDAP connection is anonymous or not
     *
     * @var boolean
     */
    private $_anonymous = true;

    /**
     * Connect to a given LDAP host with the qualifications stated
     *
     * @param $ldapBindDn - LDAP directory bind
     * @param $ldapPass - Password (not needed for anon. bind)
     */
    public function __construct($ldapBindDn, $ldapPass)
    {
        // Use secure option if using authenticated LDAP
        $ldapHost = ($ldapBindDn == '' || $ldapPass == '') ? self::LDAP_SERVER : self::SECURE_LDAP_SERVER;

        $this->_anonymous = ($ldapBindDn == '' || $ldapPass == '');

        // Connect to the resource
        $resource = @ldap_connect($ldapHost);

        ldap_set_option($resource, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 0);

        if (ldap_errno($resource) != 0) {
            throw new Ncstate_Service_Exception(ldap_error($resource), ldap_errno($resource));
        }

        if (!@ldap_bind($resource, $ldapBindDn, $ldapPass)) {
            throw new Ncstate_Service_Exception(ldap_error($resource), ldap_errno($resource));
        }

        $this->_link = $resource;
    }

    /**
     * Runs a standard search on LDAP
     *
     * @param string $queryString
     * @param string $context
     * @return beautified LDAP result set
     * @throws Ncstate_Service_Exception on LDAP Error
     */
    public function search($queryString, $context, $returnFields, $sortKey = null, $sortOrder = null)
    {
        // Make sure there are some return fields set
        if (count($returnFields) == 0) {
            $returnFields = array('*','+');
        }

        // Do the search
        $ldapResult = @ldap_search($this->getLink(), $context, $queryString, $returnFields, 0, $this->_maxResults);

        if (!$ldapResult) {
            throw new Ncstate_Service_Exception(ldap_error($this->_link), ldap_errno($this->_link));
        }

        // Return empty array if there are no etries
        if (@ldap_count_entries($this->getLink(), $ldapResult) == 0) {
            return array();
        }

        $result = @ldap_get_entries($this->getLink(), $ldapResult);

        unset($result["count"]);

        $ret = array();
        foreach ($result as $r) {
            $temp = array();

            $keys = array_keys($r);

            foreach ($keys as $k) {

                if (!is_int($k) && $k != "dn" && $k != "count") {
                    if (is_array($r[$k])) {
                        $temp[$k] = $r[$k][0];
                    } else {
                        $temp[$k] = $r[$k];
                    }
                }
            }

            $ret[] = $temp;
        }

        if (!is_null($sortKey)) {
            usort($ret, create_function('$a, $b', "return strnatcasecmp(\$a['$sortKey'], \$b['$sortKey']);"));

            if ($sortOrder == "desc") {
               $ret = array_reverse($ret);
            }
        }

        return $ret;
    }

    /**
     * Returns anonymous flag
     *
     * @return boolean
     */
    public function isAnonymous()
    {
        return $this->_anonymous;
    }

    /**
     * Returns the LDAP resource
     *
     * @return resource
     */
    public function getLink()
    {
        return $this->_link;
    }
}