<?php
/**
 * Service class to wrap the newest version of Remedy that is used for
 * tracking campus support calls.
 *
 * @see remedy.ncsu.edu/project
 */
class Ncstate_Service_Remedy
{
    /**
     * API base URL
     */
    const URI_BASE = 'https://remedyservice.oit.ncsu.edu/arsys/WSDL/public/ars00srv';

    /**
     * Remedy Username
     *
     * @var string
     */
    protected $_username = null;

    /**
     * Remedy Password
     *
     * @var string
     */
    protected $_password = null;

    /**
     * Stores the soap client
     *
     * @var stdClass
     */
    protected $_soapClient = null;

    /**
     * Constructor
     *
     * @param string $username - Remedy Username
     * @param string $password - Remedy Password
     */
    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * Retrieve a specific call specified by call-id.
     *
     * @param int $callId
     */
    public function callGet($callId)
    {
        $args = array(
            'call_id' => str_pad($callId, 8, '0', STR_PAD_LEFT),
        );

        $result = $this->_request('calls', 'get-entry', $args);

        $result->{'problem_text'} = $this->_parseDigest($result->{'problem_text'});

        return $result;
    }

    /**
     * Retrieve a list of calls for all calls matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|string) $startRecord
     * @param (null|string) $maxLimit
     */
    public function callList($qualification, $startRecord = '', $maxLimit = '')
    {
        $args = array(
            'qualification' => $qualification,
            'start_record'  => $startRecord,
            'max_limit'     => $maxLimit,
        );

        return $this->_request('calls', 'get-list', $args);
    }

    /**
     * Update the call specified by call-id.
     *
     * @param int $callId
     * @param array $data
     */
    public function callUpdate($callId, array $data)
    {
        $defaults = array(
            'action'           => null,     // action
            'call_id'          => str_pad($callId, 8, '0', STR_PAD_LEFT),  // call ID to update
            'customer_id'      => null,     // customer CID number
            'date_nextcontact' => null,     // date-time field, call next-contact
            'impact'           => null,     // impact
            'on_site_visit'    => null,     // Yes/No/NULL
            'origin'           => null,     // origin
            'owner_id'         => null,     // owner id number
            'owner'            => null,     // call owner (Remedy login)
            'priority'         => null,     // priority
            'problem_text'     => null,     // single diary entry
            'problem'          => null,     // problem description
            'product_id'       => null,     // product id number
            'product'          => null,     // product name
            'solution_id'      => null,     // solution id number
            'status'           => null,     // status
            'time_spent'       => null,     // time spent, in seconds
            'workgroup_id'     => null,     // workgroup id number
            'workgroup'        => null,     // workgroup name
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            }
        }

        return $this->_request('calls', 'update-entry', $args);
    }

    /**
     * Create a new call using the specified fields.
     *
     * @param array $data
     */
    public function callCreate(array $data)
    {
        $defaults = array(
            'action'           => null,              // action
            'agent'            => $this->_username,  // required, agent
            'comments'         => null,              // call ID to update
            'customer_id'      => null,              // customer CID number
            'date_nextcontact' => null,              // date-time field, call next-contact
            'impact'           => null,              // impact
            'on_site_visit'    => null,              // Yes/No/NULL
            'origin'           => null,              // origin
            'owner_id'         => null,              // owner id number
            'owner'            => null,              // call owner (Remedy login)
            'priority'         => null,              // priority
            'problem_text'     => null,              // single diary entry
            'problem'          => null,              // problem description
            'product_id'       => null,              // product id number
            'product'          => null,              // product name
            'solution_id'      => null,              // solution id number
            'status'           => null,              // status
            'time_spent'       => null,              // time spent, in seconds
            'workgroup_id'     => null,              // workgroup id number
            'workgroup'        => null,              // workgroup name
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            }
        }

        if (!isset($args['impact'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "impact" is required and not set');
        }

        if (!isset($args['origin'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "origin" is required and not set');
        }

        if (!isset($args['priority'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "priority" is required and not set');
        }

        if (!isset($args['problem'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "problem" is required and not set');
        }

        if (!isset($args['status'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "status" is required and not set');
        }

        if (!isset($args['workgroup_id']) && !isset($args['workgroup'])) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('Field for "workgroup" or "workgroup_id" is required and not set');
        }

        return $this->_request('calls', 'create-entry', $args);
    }

    /**
     * Retrieve call and related customer data for the specified call-id.
     *
     * @param string $entryId
     */
    public function callAttachmentGet($entryId)
    {
        $args = array(
            'entry_id' => $entryId
        );

        try{
            $result = $this->_request('calls-attachments', 'get-entry', $args);
        } catch (Ncstate_Service_Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * Retrieve the attachment records associated with the specified call-id.
     *
     * @param int $callId
     */
    public function callAttachmentList($callId)
    {
        $args = array(
            'call_id'      => str_pad($callId, 8, '0', STR_PAD_LEFT),
            'start_record' => null,
            'max_limit'    => null,
        );

        return $this->_request('calls-attachments', 'get-list-entry', $args);
    }

    /**
     * Create an attachment associated with the specified call-id.
     *
     * @param string $callId
     * @param array $data
     */
    public function callAttachmentCreate($callId, array $data)
    {
        $defaults = array(
            'attachment_data' => null,     // required, Base64 encoded attachment
            'attachment_name' => null,     //required, name of the attachment
            'attachment_size' => null,     //required, size of the attachment, in bytes
            'call_id'         => str_pad($callId, 8, '0', STR_PAD_LEFT),  //required, id number of the call that this attachment is to be associated with. Value must be zero-padded on the left to a length of 8 characters
            'type'            => null,     //required, Email / Solution
            'status'          => null,     //required, Received / Outgoing / Sent / Hold / Solution
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            } else {
                require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Field for "' . $key . '" is required and not set');
            }
        }

        return $this->_request('calls-attachments', 'create-entry', $args);
    }

    /**
     * Retrieves the specified history entry.
     *
     * @param string $entryId
     */
    public function callHistoryGet($entryId)
    {
        $args = array(
            'entry_id' => $entryId,
        );

        return $this->_request('calls-history', 'get-entry', $args);
    }

    /**
     * Retrieves all of the history entries associated with the specified call-id.
     *
     * @param int $callId
     */
    public function callHistoryList($callId, $max_limit = null, $start_record = null)
    {
        $args = array(
            'call_id'      => str_pad($callId, 8, '0', STR_PAD_LEFT),
            'max_limit'    => $max_limit,
            'start_record' => $start_record,
        );

        return $this->_request('calls-history', 'get-list-entry', $args);
    }

    /**
     * Retrieves information about a specified customer by Campus ID
     *
     * @param string $cid
     */
    public function customerGetByCID($cid)
    {
        $args = array(
            'cid'   => $cid,
            'login' => null,
        );

        return $this->_request('customers', 'get-entry', $args);
    }

    /**
     * Retrieves information about a specified customer by Unity ID
     *
     * @param string $login
     */
    public function customerGetByLogin($login)
    {
        $args = array(
            'login' => $login,
            'cid'   => null,
        );

        return $this->_request('customers', 'get-entry', $args);
    }

    /**
     * Retrieve information about the specified group by Workgroup ID
     *
     * @param string $workgroupId
     */
    public function workgroupGetByID($workgroupId)
    {
        $args = array(
            'group_id'   => $workgroupId,
            'group_name' => null,
        );

        return $this->_request('workgroups', 'get-entry', $args);
    }

    /**
     * Retrieve information about the specified group by Workgroup Name
     *
     * @param string $workgroupName
     */
    public function workgroupGetByName($workgroupName)
    {
        $args = array(
            'group_name' => $workgroupName,
            'group_id'   => '-1',
        );

        return $this->_request('workgroups', 'get-entry', $args);
    }

    /**
     * Retrieve fields from group entries matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|int) $startRecord
     * @param (null|int) $maxLimit
     */
    public function workgroupList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
        );

        if (!is_null($startRecord)) {
            $args['start_record'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['max_limit'] = $maxLimit;
        }

        return $this->_request('workgroups', 'get-list-entry', $args);
    }

    /**
     * Retrieve information about the specified user by user ID number
     *
     * @param int $userId
     */
    public function userGetByUserID($userId)
    {
        $args = array(
            'user_id' => $userId,
        );

        return $this->_request('users', 'get-entry', $args);
    }

    /**
     * Retrieve information about the specified user by Remedy login name
     *
     * @param string $login
     */
    public function userGetByLogin($login)
    {
        $args = array(
            'login_name' => $login,
        );

        return $this->_request('users', 'get-entry', $args);
    }

    /**
     * Validates the given credentials
     *
     * @param array $credentials
     */
    public function validateCredentials($credentials) {
        $args = array(
            'login_name' => $credentials['username'],
            'password'   => $credentials['password'],
        );

        return $this->_request('users', 'validate-credentials', $args);
    }

    /**
     * Retrieve user-id of user entries matching the specified qualification.
     *
     * @param string $qualification
     * @param (int|null) $startRecord
     * @param (int|null) $maxLimit
     */
    public function userList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
            'start_record'  => $startRecord,
            'max_limit'     => $maxLimit,
        );

        if (!is_null($startRecord)) {
            $args['start_record'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['max_limit'] = $maxLimit;
        }

        return $this->_request('users', 'get-list-entry', $args);
    }

    /**
     * Update the user record specified by user-id
     *
     * @param string $userId
     * @param array $data
     */
    public function userUpdate($userId, array $data)
    {
        $defaults = array(
            'availability'             => null,     // is the user available/not/retired?
            'default_notify_mechanism' => null,     // user’s default notify mechanism
            'email_address'            => null,     // 'users email address for notifications, etc.
            'email_signature'          => null,     // user’s email sig.
            'initial_query'            => null,     // query to issue automatically issue at login
            'pager_address'            => null,     // email address of user’s pager
            'pager_template'           => null,     // user’s preferred page format template
            'password'                 => null,     // Remedy login password
            'products_count'           => null,     // count of products in user’s personal products menu
            'solutions_count'          => null,     // count of solutions in user’s personal solutions menu
            'user_id'                  => $userId,  // required, user’s id number
        );

        $args = array();

        foreach ($defaults as $key => $value) {
            if (isset($data[$key])) {
                $args[$key] = $data[$key];
            } elseif (!is_null($value)) {
                $args[$key] = $value;
            }
        }

        if (!isset($args['user_id'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Field for "user_id" is required and not set');
        }

        return $this->_request('users', 'update-entry', $args);
    }

    /**
     * Retrieve a single solution specified by solution-id
     *
     * @param string $solutionId
     */
    public function solutionGet($solutionId)
    {
        $args = array(
            'solution_id' => $solutionId,
        );

        return $this->_request('solutions', 'get-entry', $args);
    }

    /**
     * Retrieve a list of solution-ids that match the specified qualification.
     *
     * Note that queries against this form may return the same solution-id
     * multiple times if the qualification includes multiple keywords.
     *
     * @param string $qualification
     * @param (null|int) $startRecord
     * @param (null|int) $maxLimit
     */
    public function solutionList($qualification, $startRecord = null, $maxLimit = null, $withKeywords = true)
    {
        $args = array(
            'qualification' => $qualification,
        );

        if (!is_null($startRecord)) {
            $args['start_record'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['max_limit'] = $maxLimit;
        }

        if ($withKeywords) {
            return $this->_request('solutions', 'get-list-entry', $args);
    } else {
            return $this->_request('solutions', 'get-listNoKWDS', $args);
        }
    }

    /**
     * Retrieve list of Survey entries matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|int) $startRecord
     * @param (null|int) $maxLimit
     */
    public function surveyList($qualification, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'qualification' => $qualification,
        );

        if (!is_null($startRecord)) {
            $args['start_record'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['max_limit'] = $maxLimit;
        }

        return $this->_request('surveys', 'get-list-entry', $args);
    }

    /**
     * Retrieve information from the calls_cust form for all calls matching the specified qualification.
     *
     * @param string $qualification
     * @param (null|string) $startRecord
     * @param (null|string) $maxLimit
     */
    public function callCustList($qualification, $startRecord = '', $maxLimit = '')
    {
        $args = array(
            'qualification' => $qualification,
            'start_record'  => $startRecord,
            'max_limit'     => $maxLimit,
        );

        $result = $this->_request('calls-cust', 'get-list-entry', $args);

        if (is_array($result->getListValues)) {
            foreach ($result->getListValues as &$r) {
                if (isset($r->{'problem_text'})) {
                    $r->{'problem_text'} = $this->_parseDigest($r->{'problem_text'});
                }
            }

            unset($r);

        } else {
            if (isset($result->getListValues->{'problem_text'})) {
                $result->getListValues->{'problem_text'} = $this->_parseDigest($result->getListValues->{'problem_text'});
            }
        }

        unset($r);

        return $result;
    }


    /**
     * Retrieve a list of the keywords associated with the specified solution.
     *
     * @param int $solutionId
     * @param (int|null) $startRecord
     * @param (int|null) $maxLimit
     */
    public function keywordList($solutionId, $startRecord = null, $maxLimit = null)
    {
        $args = array(
            'solution_id' => $solutionId,
            'startRecord'  => $startRecord,
            'maxLimit'     => $maxLimit,
        );

        if (!is_null($startRecord)) {
            $args['startRecord'] = $startRecord;
        }

        if (!is_null($maxLimit)) {
            $args['maxLimit'] = $maxLimit;
        }

        return $this->_request('keywords', 'get-list', $args);
    }

    /**
     * Increment one of the usage counters associated with Remedy solutions
     *
     * @param string $fieldName
     * @param string $service
     * @param int $solutionId
     */
    public function solutionIncrement($fieldName, $service, $solutionId)
    {
        $args = array(
            'field_name'  => $fieldName,
            'service'     => $service,
            'solutionid' => $solutionId,
        );

        return $this->_request('solutions-inc-counter', 'increment', $args);
    }

    /**
	* Sends a request using curl to the required URI
	*
	* @param string $method Untappd method to call
	* @param array $args key value array or arguments
	*
	* @throws Awsm_Service_Untappd_Exception
	*
	* @return stdClass object
	*/
    protected function _request($wsdlEndpoint, $method, $args)
    {
        $soapArgs = new stdClass();

        // remove any unnecessary args from the query string
        foreach ($args as $key => $a) {
            $soapArgs->$key = ($a != '' && !is_null($a)) ? $a : '';
        }
        $wsdl = self::URI_BASE . '/' . $wsdlEndpoint;

        $this->_soapClient = new SoapClient($wsdl, array('trace' => true));

        $headers = array();

        $authHeader = new stdClass();
        $authHeader->userName = $this->_username;
        $authHeader->password = $this->_password;

        $authInfoHeader = new SoapVar($authHeader, SOAP_ENC_OBJECT, 'AuthenticationInfo');

        $headers[] = new SoapHeader('AuthenticationInfo', 'AuthenticationInfo', $authInfoHeader);

        $this->_soapClient->__setSoapHeaders($headers);

        try {
            $result = $this->_soapClient->{$method}($soapArgs);
        } catch (Exception $e) {
            require_once 'Ncstate/Service/Exception.php';
            throw new Ncstate_Service_Exception('SOAP Error: ' . $e->getMessage());
        }
        return $result;
    }

    protected function _parseDigest($digest)
    {
        $entries = preg_split('/\x{f8e2}/u', $digest);

        $parsed = array();
        foreach ($entries as $e) {
            if (strlen(trim($e)) != 0) {
                $attributes = preg_split('/\x{f8e3}/u', $e);

                $entry = new stdClass();
                $entry->timestamp = $attributes[0];
                $entry->userName  = $attributes[1];
                $entry->entry     = $attributes[2];

                $parsed[] = $entry;
            }
        }

        return $parsed;
    }

    /**
     * Gets the soap client used in the service
     *
     * @return null|SoapClient object
     */
    public function getSoapClient()
    {
        return $this->_soapClient;
    }
    
     /**
     * Prepare mail data for a specific call
     *
     * @param int $callId Call ID message to use in subject line
     * @param array $params Mail array( <to>, <from>, <subject>, <body>, [headers] )
     *        to		=> string
     *        from      => string
     *        subject   => string|null
     *        body      => string
     *        headers   => array
     * @param boolean $sendmail Should we make a call to $this->callSendMail() instead of returning data
     *
     * @throws Ncstate_Service_Exception
     *
     * @return array|boolean
     */
    public function callPrepareMail($callId, array $params, $sendmail=false)
    {
        // Grab the call to ensure it is a valid call id, and to get the workgroup
        $call = $this->callGet($callId);
        
        // Simple parameter validation (RRFC2822 is WAY too complicated to implement a complete
        // validation, and php's filter_var function doesn't allow things like "User <user@host.com>")
        if (!isset($params['to'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Missing recipient address');
        }
        if (!isset($params['from'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Missing sender address');
        }
        if (!isset($params['subject'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Missing message subject');
        }
        if (!isset($params['body'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Missing message body');
        }
        if (isset($params['headers']) && !is_array($params['headers'])) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Headers must be passed as an array');
        }
        
        // Build the subject line
        if (empty($params['subject'])) {
            $params['subject'] = $call->problem;
        }
        $subject = sprintf('Call %d: %s', $callId, $params['subject']);
        
        // Build the mail headers
        $headers   = array();
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-type: text/plain; charset=iso-8859-1';
        $headers[] = sprintf('From: %s', $params['from']);
        if (isset($params['cc'])) {
			$headers[] = sprintf('Cc: %s', $params['cc']);
		}
        if (isset($params['bcc'])) {
			$headers[] = sprintf('Bcc: %s', $params['bcc']);
		}
        $headers[] = sprintf('Reply-To: Remedy <%s@remedy.ncsu.edu>', $call->workgroup);
        $headers[] = "Subject: $subject";
        $headers[] = 'X-Mailer: PHP/' . phpversion();
        
        // Add the user defined headers to the end
        if (isset($params['headers'])) {
            foreach ($params['headers'] as $header) {
                $headers[] = preg_replace('/[\r\n]/i', '', $header);
            }
        }
        
        // Build the output array
        $maildata = array(
            'to'      => $params['to'],
            'subject' => $subject,
            'body'    => strip_tags($params['body']),
            'headers' => implode("\r\n", $headers),
        );
        
        // Call sendmail or just return the data
        if ($sendmail) {
			return $this->callSendMail($maildata);
		}
        return $maildata;
    }
    
    /**
     * Send mail using php's default mailer, and update the remedy call
     *
     * @param array $data Data array from $this->callPrepareMail()
     * @param boolean $updateRemedy Should the Remedy call be updated with the mail data
     *
     * @throws Ncstate_Service_Exception
     *
     * @return boolean
     */
    public function callSendMail(array $data, $updateRemedy=true)
    {
        // Sanity Check
        foreach (array('to', 'subject', 'body', 'headers') as $req) {
            if (!isset($data[$req])) {
                require_once 'Ncstate/Service/Exception.php';
                    throw new Ncstate_Service_Exception('Invalid mail data');
            }
        }
        if (!preg_match('/^Call ([0-9]+):/i', $data['subject'], $match)) {
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception('Invalid subject line');
        }
        $callId = $match[1];
        
        // Verify the call exists
        $call = $this->callGet($callId);
        
        // Send the email
        $result = mail($data['to'], $data['subject'], $data['body'], $data['headers']);
        
        // Verify message queued successfully
        if (!$result) {
            $error = error_get_last();
            require_once 'Ncstate/Service/Exception.php';
                throw new Ncstate_Service_Exception(sprintf('Unable to send mail: %s', $error['message']));
        }
        if (!$updateRemedy) {
			return true;
		}
        
        // Check for from, Cc and Bcc
        $from = '';
        foreach(explode("\r\n", $data['headers']) as $header) {
            $header = explode(': ',$header);
            switch($header[0]) {
                case 'From':
                    $from = $header[1];
                    break;
                case 'Cc':
                    $cc = $header[1];
                    break;
                case 'Bcc':
                    $bcc = $header[1];
                    break;
            }
        }
        
        // Assemble text
        $message = sprintf("From: %s\nTo: %s\n", $from, $data['to']);
        if (isset($cc)) {
			$message .= "CC: $cc\n";
		}
        if (isset($bcc)) {
			$message .= "BCC: $bcc\n";
		}
        $message .= sprintf("Subject: %s\n\n%s", substr($data['subject'], strpos($data['subject'],':')+2), $data['body']);
        
        // Update remedy call
        $this->callUpdate($callId, array('problem_text' => $message));
        return true;
    }
}