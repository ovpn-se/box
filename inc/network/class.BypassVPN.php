<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-20
 * Time: 12:48
 */

namespace Network;


class BypassVPN {

    /**
     * Purges all IPs that are currently bypassed.
     *
     * @return bool
     */
    public static function purge() {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        // Remove all IPs that are currently bypassed.
        unset($config['ovpn_bypass']);
        write_config($config);
        return true;
    }

    /**
     * Activates bypass for a specific IP
     *
     * @param $ip
     * @return bool
     */
    public static function activate($ip) {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        if (!isset($config['ovpn_bypass'])) {
            $config['ovpn_bypass']['hosts']=array();
        }

        // Check so the IP isn't currently being bypassed.
        if (!in_array($ip, $config['ovpn_bypass']['hosts'])) {

            // The IP isn't currently being bypassed so let's add it to the configuration file.
            $config['ovpn_bypass']['hosts'][]=$ip;
            write_config($config);
            filter_configure();
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
    function deactivate($ip) {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        // Check so the bypass array exists in pfSenses configuration
        if (isset($config['ovpn_bypass'])) {

            // Loop through all hosts
            foreach ($config['ovpn_bypass']['hosts'] as $k => $v) {

                // Check whether the host is the requsted IP
                if ($v == $ip) {

                    // Remove host from array and update the configuration file.
                    unset($config['ovpn_bypass']['hosts'][$k]);
                    write_config($config);
                    filter_configure();
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    /**
     * Returns a list of all the IPs that currently bypass the VPN connection
     *
     * @return bool
     */
    function show() {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        // Check so the bypass array exists in pfSenses configuration
        if (isset($config['ovpn_bypass'])) {
            return $config['ovpn_bypass']['hosts'];
        }

        return false;

    }

} 