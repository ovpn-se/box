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

// Import pfSense functionality
//include ('functions.php');

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
                $config['openvpn']['openvpn-client'][$key]['custom_options'] = 'remote ' . $ip . ' ' . $ports[1] . ';remote-random;remote-cert-tls server;reneg-sec 432000;mute-replay-warnings;replay-window 256;tls-auth /opt/ovpn/keys/ovpn-tls.key 1;log-append /tmp/openvpn.log';

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
    global $g, $config;

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

function activatePortForwading($ip, $port, $proto)
{
    // Make the config variable accessible
    global $g, $config;

    $proto = strtolower($proto);

    // Check so the input is a valid IP address
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        return false;
    }

    // Check so the input is a valid port and between 1026 - 65534
    if (!is_numeric($port) || $port > 65534 || $port < 1) {
        return false;
    }

    // Check so the input is valid: udp,tcp or both
    if (!in_array($proto, array('udp', 'tcp', 'both'))) {
        return false;
    }

    // Create array is it doesn't already exist
    if (!isset($config['ovpn']['ovpn_ports'])) {
        $config['ovpn']['ovpn_ports'] = array();
    }

    // Verify that the port forward doesn't already exist
    if(!empty($config['ovpn']['ovpn_ports'])) {
        foreach($config['ovpn']['ovpn_ports'] as $entry) {
            if($entry['port'] == $port) {
                return false;
            }
        }
    }

    $config['ovpn']['ovpn_ports'][] = array(
        'ip' => $ip,
        'port' => $port,
        'type' => $proto
    );

    \write_config('Added port forward for host', false, true);
    shell_exec('/etc/rc.filter_configure_sync');
    shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));

    return true;

}

function deactivatePortForwarding($ip,$port,$proto)
{
    // Make the config variable accessible
    global $g, $config;

    if (isset($config['ovpn']['ovpn_ports']) && !empty($config['ovpn']['ovpn_ports'])) {
        foreach ($config['ovpn']['ovpn_ports'] as $key => $entry) {
            if ($entry['ip'] == $ip && $entry['port'] == $port && $entry['type'] == $proto) {
                unset($config['ovpn']['ovpn_ports'][$key]);
                \write_config('Removed port forward for host', false, true);
                shell_exec('/etc/rc.filter_configure_sync');
                shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));
                return true;
            }
        }
    }

    return false;
}

function showPortForwards()
{

    // Make the config variable accessible
    global $g, $config;

    if (!isset($config['ovpn']['ovpn_ports']) || empty($config['ovpn']['ovpn_ports'])) {
        return false;
    }

    $retVal = array();

    foreach($config['ovpn']['ovpn_ports'] as $entry) {

        $key = md5($entry['ip']);

        if(!isset($retVal[$key])) {
            $retVal[$key] = array();
        }

        $retVal[$key][] = $entry;
    }

    return $retVal;
}

function purgePortForwards()
{
    // Make the config variable accessible
    global $g, $config;

    if (isset($config['ovpn']['ovpn_ports'])) {
        unset($config['ovpn']['ovpn_ports']);
        \write_config('Purged all port forwards', false, true);
        shell_exec('/etc/rc.filter_configure_sync');
        shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));
    }

    return true;
}

/**
 * Purges all IPs that are currently bypassed.
 *
 * @return bool
 */
function purgeVPNBypass()
{
    // Make the config variable accessible
    global $g, $config;

    if (isset($config['ovpn']['bypass'])) {
        unset($config['ovpn']['bypass']);
        \write_config('Purged all IP addresses that bypass VPN', false, true);
        shell_exec('/etc/rc.filter_configure_sync');
        shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));
    }

    return true;
}

/**
 * Activates bypass for a specific IP
 *
 * @param $ip
 * @return bool
 */
function activateVPNBypass($ip) {

    // Make the config variable accessible
    global $g, $config;

    if (!isset($config['ovpn']['bypass'])) {
        $config['ovpn']['bypass'] = array();
    }


    // Check so the IP isn't currently being bypassed.
    if (!in_array($ip, $config['ovpn']['bypass'])) {

        // The IP isn't currently being bypassed so let's add it to the configuration file.
        $config['ovpn']['bypass'][] = $ip;
        \write_config('Added IP address to bypass VPN', false, true);
        shell_exec('/etc/rc.filter_configure_sync');
        shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));
        return true;
    }

    return false;
}

/**
 * Deactivates bypass for a specific IP
 *
 * @param $ip
 * @return bool
 */
function deactivateVPNBypass($ip) {

    // Make the config variable accessible
    global $g, $config;

    // Check so the bypass array exists in pfSenses configuration
    if (isset($config['ovpn']['bypass']) && !empty($config['ovpn']['bypass'])) {

        // Loop through all hosts
        foreach ($config['ovpn']['bypass'] as $k => $v) {

            // Check whether the host is the requsted IP
            if ($v == $ip) {

                // Remove host from array and update the configuration file.
                unset($config['ovpn']['bypass'][$k]);
                \write_config('Removed IP address to bypass VPN', false, true);
                shell_exec('/etc/rc.filter_configure_sync');
                shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));
                return true;
            }
        }
    }

    return false;
}

/**
 * Returns a list of all the IPs that currently bypass the VPN connection
 *
 * @return bool
 */
function showVPNBypassHosts() {

    // Make the config variable accessible
    global $g, $config;

    // Check so the bypass array exists in pfSenses configuration
    if (isset($config['ovpn']['bypass'])) {
        return $config['ovpn']['bypass'];
    }

    return false;

}