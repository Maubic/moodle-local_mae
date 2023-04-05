<?php
/**
 * Version details
 *
 * @package    local_mae
 * @copyright  2021 Maubic Consultoría Tecnológica SL
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero GPL v3 or later
 */


define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');
require_once("$CFG->libdir/externallib.php");

if (!$CFG->enablewebservices) {
    throw new moodle_exception('enablewsdescription', 'webservice');
}


class local_mae_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function impersonate_parameters() {
        return new external_function_parameters(
            array(
                 /*
                'scorm_student_details' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'id of course'),
                            'name' => new external_value(PARAM_TEXT, 'multilang compatible name, course unique'),
                            'description' => new external_value(PARAM_RAW, 'group description text'),
                            'enrolmentkey' => new external_value(PARAM_RAW, 'group enrol secret phrase'),
                        )
                    )
                )
                */
                'scorm_student_id' => new external_value(PARAM_USERNAME, 'student_id description text')
            )
        );
    }

    public static function impersonate_returns() {
//        return new external_multiple_structure(
//          new external_single_structure(...
        return
            new external_single_structure(
                array(
                    'token' => new external_value(PARAM_RAW, 'group record id'),
                    'private' => new external_value(PARAM_RAW, 'id of course'),
                )
            );
    }


    /**
     * Create groups
     * @param array $groups array of group description arrays (with keys groupname and courseid)
     * @return array of newly created groups
     */
    public static function impersonate($scorm_student_id) { //Don't forget to set it as static
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");

        require_capability('mod/mae:impersonate', context_system::instance());

        if ($CFG->version > 2022112803) { // 2022112802
            throw new moodle_exception('Moodle version ' . $CFG->version . ' not compatible.');
        }
        if ($CFG->version < 2018050800) {
            throw new moodle_exception('Moodle version ' . $CFG->version . ' too old.');
        }
        
        $params = self::validate_parameters(self::impersonate_parameters(), array('scorm_student_id'=>$scorm_student_id));
        $serviceshortname  = 'moodle_mobile_app';

        $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollbacked.

        $username = trim(core_text::strtolower($params['scorm_student_id']));

        if (is_restored_user($username)) {
            throw new moodle_exception('restoredaccountresetpassword', 'webservice');
        }
        
        $systemcontext = context_system::instance();

        require_once("$CFG->libdir/authlib.php");

        if ($user = get_complete_user_data('username', $username, $CFG->mnet_localhost_id)) {
            // we have found the user
    
        } else if (!empty($CFG->authloginviaemail)) {
            if ($email = clean_param($username, PARAM_EMAIL)) {
                $select = "mnethostid = :mnethostid AND LOWER(email) = LOWER(:email) AND deleted = 0";
                $params = array('mnethostid' => $CFG->mnet_localhost_id, 'email' => $email);
                $users = $DB->get_records_select('user', $select, $params, 'id', 'id', 0, 2);
                if (count($users) === 1) {
                    // Use email for login only if unique.
                    $user = reset($users);
                    $user = get_complete_user_data('id', $user->id);
                    $username = $user->username;
                }
                unset($users);
            }
        }        

        if (!empty($user)) {

            // Cannot authenticate unless maintenance access is granted.
            $hasmaintenanceaccess = has_capability('moodle/site:maintenanceaccess', $systemcontext, $user);
            if (!empty($CFG->maintenance_enabled) and !$hasmaintenanceaccess) {
                throw new moodle_exception('sitemaintenance', 'admin');
            }
        
            if (isguestuser($user)) {
                throw new moodle_exception('noguest');
            }

            if (empty($user->confirmed)) {
                throw new moodle_exception('usernotconfirmed', 'moodle', '', $user->username);
            }
        
            // setup user session to check capability
            \core\session\manager::set_user($user);
      
            //check if the service exists and is enabled
            $service = $DB->get_record('external_services', array('shortname' => $serviceshortname, 'enabled' => 1));
            if (empty($service)) {
                // will throw exception if no token found
                throw new moodle_exception('servicenotavailable', 'webservice');
            }
         
            // Get an existing token or create a new one.
            $token = external_generate_token_for_current_user($service);
            $privatetoken = $token->privatetoken;
            external_log_token_request($token);
        
            $siteadmin = has_capability('moodle/site:config', $systemcontext, $USER->id);
        
            $usertoken = new stdClass;
            $usertoken->token = $token->token;
            // Private token, only transmitted to https sites and non-admin users.
            if (is_https() and !$siteadmin) {
                $usertoken->privatetoken = $privatetoken;
            } else {
                $usertoken->privatetoken = null;
            }
            //echo json_encode($usertoken);
        } else {
            throw new moodle_exception('invalidlogin');
        }

        $transaction->allow_commit();

        $response = array();
        $response['token'] = $usertoken->token;
        $response['private'] = $usertoken->privatetoken;

        return $response;
    }

/* ******************************************************** */

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function find_scoid_parameters() {
        return new external_function_parameters(
            array(
                'type' => new external_value(PARAM_TEXT, 'unit | tutorial'),
                'level' => new external_value(PARAM_INT, 'level number'),
                'unit' => new external_value(PARAM_INT, 'unit number'),
                'username' => new external_value(PARAM_TEXT, 'username', VALUE_DEFAULT, 'all'),
            )
        );
    }

    public static function find_scoid_returns() {
        return  new external_multiple_structure(
	    new external_single_structure(
                array(
                    'scormid' => new external_value(PARAM_RAW, 'scormid'),
                    'scoid' => new external_value(PARAM_RAW, 'scoid'),
                )
            )
        );
    }


/**
     * find_scoid
     * @param array $groups array of group description arrays (with keys groupname and courseid)
     * @return array of newly created groups
     */
    public static function find_scoid($type, $level, $unit, $username='') { //Don't forget to set it as static
        global $CFG, $DB;
        require_once("$CFG->dirroot/group/lib.php");
        $un = ($type=='unit')?"un":"tu";
        $type = ($type=='unit')?"engine":"tutorias";
        $level = (int) $level;
        $unit = (int) $unit;
        $quest = '_';
        //$username = $username;
	if ($username <> 'all') {
		$sql = "select * from {user} us 
			inner join {role_assignments} ra on ra.userid=us.id
 			inner join {context} ctx on ctx.id=ra.contextid
 			inner join {course} course on course.id=ctx.instanceid
 			inner join {scorm} scorm on course.id=scorm.course
 			inner join {scorm_scoes} scoes on scoes.scorm=scorm.id
			WHERE 
 			us.username='${username}' 
			and scoes.launch like '${type}.html${quest}ni=${level}&$un=${unit}&%'";
	} else {
 	       $sql = "SELECT *
 	       FROM {scorm_scoes}
 	       WHERE launch like '${type}.html${quest}ni=${level}&$un=${unit}&%'";
	}
        $rs = $DB->get_recordset_sql($sql);
	$responses = [];
        foreach ($rs as $sco) {
            $response['scoid'] = $sco->id;
            $response['scormid'] = $sco->scorm;
	    $responses[] = $response;
        }

        return $responses;

    }




}
