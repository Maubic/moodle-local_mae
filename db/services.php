<?php
/**
 *
 * @package    local_mae
 * @copyright  2021 Maubic Consultoría Tecnológica SL
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero GPL v3 or later
 * 
 */

  $services = array(
    'maeservice' => array(                                                // the name of the web service
        'functions' => array ('local_mae_impersonate', 'local_mae_find_scoid'), // web service functions of this service
        'requiredcapability' => 'mod/mae:impersonate',                // if set, the web service user need this capability to access 
                                                                            // any function of this service. For example: 'some/capability:specified'                 
        'restrictedusers' => 0,                                             // if enabled, the Moodle administrator must link some user to this service
                                                                            // into the administration
        'enabled' => 1,                                                       // if enabled, the service can be reachable on a default installation
        'shortname' =>  'mae',       // optional – but needed if restrictedusers is set so as to allow logins.
        'downloadfiles' => 0,    // allow file downloads.
        'uploadfiles'  => 0      // allow file uploads.
     )
);

$functions = array(
    'local_mae_impersonate' => array(         //web service function name
        'classname'   => 'local_mae_external',  //class containing the external function OR namespaced class in classes/external/XXXX.php
        'methodname'  => 'impersonate',          //external function name
        'classpath'   => 'local/mae/externallib.php',  //file containing the class/external function - not required if using namespaced auto-loading classes.
                                                   // defaults to the service's externalib.php
        'description' => 'Impersonate as a student to insert his progress.',    //human readable description of the web service function
        'type'        => 'write',                  //database rights of the web service function (read, write)
        'ajax' => true,        // is the service available to 'internal' ajax calls. 
        'services' => array('maeservice'),    // Optional, only available for Moodle 3.1 onwards. List of built-in services (by shortname) where the function will be included.  Services created manually via the Moodle interface are not supported.
        'capabilities' => 'mod/mae:impersonate', // comma separated list of capabilities used by the function.
    ),
    'local_mae_find_scoid' => array(         //web service function name
        'classname'   => 'local_mae_external',  //class containing the external function OR namespaced class in classes/external/XXXX.php
        'methodname'  => 'find_scoid',          //external function name
        'classpath'   => 'local/mae/externallib.php',  //file containing the class/external function - not required if using namespaced auto-loading classes.
                                                   // defaults to the service's externalib.php
        'description' => 'Get SCOID for a MAE Level.',    //human readable description of the web service function
        'type'        => 'write',                  //database rights of the web service function (read, write)
        'ajax' => true,        // is the service available to 'internal' ajax calls. 
        'services' => array('maeservice'),    // Optional, only available for Moodle 3.1 onwards. List of built-in services (by shortname) where the function will be included.  Services created manually via the Moodle interface are not supported.
        'capabilities' => 'mod/mae:impersonate', // comma separated list of capabilities used by the function.
    ),    
);