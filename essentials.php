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

// Import pfsense
include ('/etc/inc/config.inc');
include ('/etc/inc/interfaces.inc');
include ('/etc/inc/shaper.inc');
include ('/etc/inc/filter.inc');
include ('/etc/inc/ovpn.inc');
include ('/etc/inc/globals.inc');
include ('/etc/inc/functions.inc');

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

/**
 * Pfsense function to save credentials to config.xml
 *
 * @param $username
 * @param $password
 * @return bool
 */
function saveOpenVPNCredentials($username, $password)
{
    global $g, $config;

    if(!empty($config['openvpn']['openvpn-client'])) {
        foreach($config['openvpn']['openvpn-client'] as $key => $client) {
            $config['openvpn']['openvpn-client'][$key]['auth_user'] = $username;
            $config['openvpn']['openvpn-client'][$key]['auth_pass'] = $password;
            $config['openvpn']['openvpn-client'][$key]['description'] = 'OVPN - ' . ($key+1);
        }

        \write_config('Updated OpenVPN credentials', false, true);
    }

    return true;
}

/**
 * Generates openvpn configuration file
 *
 * @param $vpnID
 * @param $customID
 * @param $ip
 * @param $ports
 * @return bool
 */
function saveOpenVPNConfig($vpnID, $customID, $ip, $ports)
{
    global $g, $config;

    if(!empty($config['openvpn']['openvpn-client'])) {
        foreach($config['openvpn']['openvpn-client'] as $key => $client) {

            if($client['vpnid'] == $vpnID) {
                $config['openvpn']['openvpn-client'][$key]['ovpn_id'] = $customID;
                $config['openvpn']['openvpn-client'][$key]['protocol'] = 'UDP';
                $config['openvpn']['openvpn-client'][$key]['dev_mode'] = 'tun';
                $config['openvpn']['openvpn-client'][$key]['interface'] = 'wan';
                $config['openvpn']['openvpn-client'][$key]['server_addr'] = $ip;
                $config['openvpn']['openvpn-client'][$key]['server_port'] = $ports[0];
                $config['openvpn']['openvpn-client'][$key]['resolve_retry'] = 'yes';
                $config['openvpn']['openvpn-client'][$key]['proxy_authtype'] = 'none';
                $config['openvpn']['openvpn-client'][$key]['description'] = 'OVPN - ' . ($key+1);
                $config['openvpn']['openvpn-client'][$key]['mode'] = 'p2p_tls';
                $config['openvpn']['openvpn-client'][$key]['crypto'] = 'AES-256-CBC';
                $config['openvpn']['openvpn-client'][$key]['digest'] = 'SHA1';
                $config['openvpn']['openvpn-client'][$key]['engine'] = 'cryptodev';
                $config['openvpn']['openvpn-client'][$key]['compression'] = 'adaptive';
                $config['openvpn']['openvpn-client'][$key]['verbosity_level'] = '4';
                $config['openvpn']['openvpn-client'][$key]['custom_options'] = 'remote ' . $ip . ' ' . $ports[1] . ';remote-random;remote-cert-tls server;reneg-sec 432000;mute-replay-warnings;replay-window 256;tls-auth /var/etc/openvpn/ovpn-tls.key 1;log-append /tmp/openvpn.log';

            }


        }

        \write_config('Updated OpenVPN credentials', false, true);
        shell_exec('/etc/rc.openvpn');
    }

    return true;
}

/**
 * Enables/disables the killswitch
 *
 */
function handleKillswitch($active)
{

    // Make the config variable accessible
    global $config;

    $ovpn_wan = \get_real_interface("wan");

    if($active) {

        if(!isset($config['ovpn_killswitch']) || $config['ovpn_killswitch'] == 0) {
            $config['ovpn_killswitch'] = 1;
            \write_config('Disabled the killswitch', false, true);
            shell_exec('/etc/rc.filter_configure_sync');
            shell_exec('/sbin/pfctl -F state -i ' . $ovpn_wan);
        }
    } else {

        if(!isset($config['ovpn_killswitch']) || $config['ovpn_killswitch'] == 1) {
            $config['ovpn_killswitch'] = 0;
            \write_config('Disabled the killswitch', false, true);
            shell_exec('/etc/rc.filter_configure_sync');
            shell_exec('/sbin/pfctl -F state -i ' . $ovpn_wan);

        }
    }

    return true;
}

