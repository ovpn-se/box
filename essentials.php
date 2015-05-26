<?php

date_default_timezone_set("Europe/Stockholm");

/**
 * Log & display error messages
 */
ini_set("log_errors", 1);
ini_set("display_errors", E_ALL);
ini_set("error_log", "/tmp/php-error.log");

session_start();

/**
 * Define document root shortcut
 */
define('DOCUMENT_ROOT', dirname(__FILE__));


// so the array names, they will be ”ovpn_ports” and ”ovpn_bypass"
// ovpn_ports has ip, port, type (tcp/udp/both)
// ovpn_bypass has ip

// Import pfsense
include ('/etc/inc/config.inc');
include ('/etc/inc/interfaces.inc');
include ('/etc/inc/shaper.inc');
include ('/etc/inc/filter.inc');
include ('/etc/inc/ovpn.inc');
include ('/etc/inc/globals.inc');
include ('/etc/inc/functions.inc');

// Import pfSense functionality
include ('functions.php');

// Import Composer
require_once('vendor/autoload.php');


// Autoloader script
function my_autoloader($class) {

    /**
     * List the current namespaces with the folder name as key
     */
    $namespaces = array(
        'api'     => strpos($class, 'API'),
        'base'    => strpos($class, 'Base'),
        'network' => strpos($class, 'Network'),
        'openvpn' => strpos($class, 'OpenVPN'),
        'ovpn'    => strpos($class, 'OVPN'),
        'shell'   => strpos($class, 'Shell')
    );

    /**
     * Loop through the namespaces
     */
    foreach($namespaces as $file => $namespace) {

        // Check if the file is found
        if($namespace !== false) {

            // Create path for the file and attempt to import it
            $explode = explode('\\', $class);
            $count = count($explode)-1;
            $path = DOCUMENT_ROOT . '/inc/' . $file . '/class.' . $explode[$count] . '.php';

            // Verify the existence of the file
            if(file_exists($path)) {

                // Import & break the loop
                require_once($path);
                break;
            } else {
                //error_log($path);
            }
        }
    }
}

spl_autoload_register('my_autoloader');