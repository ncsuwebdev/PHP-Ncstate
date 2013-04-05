<?php
/**
 * Set of classes to programatically communicate with services at NC State
 * University
 *
 * @package Ncstate_Service
 * @author  Office of Information Technology - Outreach Technology
 */

/**
 * Provides an interface to the user tables in NC State's LDAP services to
 * provide information about users and accounts associated with NC State.
 *
 * @see http://www.ldap.ncsu.edu/datadetails.php#students-people
 * @see http://www.ldap.ncsu.edu/datadetails.php#employees-people
 * @see http://www.ldap.ncsu.edu/datadetails.php#accounts
 */
class Ncstate_Service_User extends Ncstate_Service_Ldap_Connector
{
    /**
     * Context for all NC State people
     *
     * @var string
     */
    const PEOPLE_CONTEXT = 'ou=people,dc=ncsu,dc=edu';

    /**
     * Context for all NC State students
     *
     * @var string
     */
    const STUDENT_CONTEXT = 'ou=students,ou=people,dc=ncsu,dc=edu';

    /**
     * Context for all NC State employees
     *
     * @var string
     */
    const EMPLOYEE_CONTEXT = 'ou=employees,ou=people,dc=ncsu,dc=edu';

    /**
     * Context for all NC State accounts, not necessarily active
     *
     * @var string
     */
    const ACCOUNT_CONTEXT = 'ou=accounts,dc=ncsu,dc=edu';

    /**
     * Searches LDAP based on a unity ID
     *
     * @param string $unityId - Unity ID to search
     * @param string $context - Context to search in, by default is for PEOPLE
     * @param array $returnFields - Array of return fields to limit the result to.  If
     *                              empty will return all fields in table.
     *
     * @return array
     *
     * @throws Ncstate_Service_Exception
     */
    public function findByUnityId($unityId, $context = self::PEOPLE_CONTEXT, $returnFields = array())
    {
        switch ($context) {
            case self::PEOPLE_CONTEXT:
            case self::STUDENT_CONTEXT:
            case self::EMPLOYEE_CONTEXT:
            case self::ACCOUNT_CONTEXT:
                break;
            default:
                throw new Ncstate_Service_Exception('Invalid context for this method passed');
                break;
        }

        return $this->search("uid=" . $unityId, $context, $returnFields);
    }

    /**
     * Searches LDAP based on a campus ID number
     *
     * @param string $campusId - Campus ID to search
     * @param string $context - Context to search in, by default is for PEOPLE
     * @param array $returnFields - Array of return fields to limit the result to.  If
     *                              empty will return all fields in table.
     *
     * @return array
     *
     * @throws Ncstate_Service_Exception
     */
    public function findByCampusId($campusId, $context = self::PEOPLE_CONTEXT, $returnFields = array())
    {
        if ($this->isAnonymous()) {
            throw new Ncstate_Service_Exception('Can not search on campus ID using anonymous LDAP access');
        }

        switch ($context) {
            case self::PEOPLE_CONTEXT:
            case self::STUDENT_CONTEXT:
            case self::EMPLOYEE_CONTEXT:
                break;
            default:
                throw new Ncstate_Service_Exception('Invalid context for this method passed');
                break;
        }

        return $this->search("ncsucampusID=" . $campusId, $context, $returnFields);
    }
}