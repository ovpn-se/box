<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-20
 * Time: 12:56
 */

namespace Network;


class Port {

    public static function purge() {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        unset($config['ovpn_forward']);
        \write_config($config);
        return true;
    }

    public static function activate($ip,$port,$proto) {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        // Check so the input is a valid IP address
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        // Check so the input is a valid port and between 1026 - 65534
        if (!is_numeric($port) || $port>65534 || $port<1025) {
            return false;
        }

        // Check so the input is valid: udp,tcp or both
        if (!in_array($proto, array('udp', 'tcp', 'both'))) {
            return false;
        }

        // Create array is it doesn't already exist
        if (!isset($config['ovpn_forward'])) {
            $config['ovpn_forward']['hosts']=array();
        }

        // Check so the port doesn't exist
        if (!in_array($port, $config['ovpn_forward']['hosts'])) {
            $portforward=array();
            $portforward['ip']=$ip;
            $portforward['port']=$port;
            $portforward['proto']=$proto;
            $config['ovpn_forward']['hosts'][]=$portforward;
            \write_config($config);
            \filter_configure();
            return true;
        }

        return false;
    }

    public static function deactivate($ip,$port,$proto) {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        if (isset($config['ovpn_forward'])) {
            foreach ($config['ovpn_forward']['hosts'] as $k => $v) {
                print $v;
                if ($v == $ip) {
                      unset($config['ovpn_forward']['hosts'][$k]);
                      \write_config($config);
                      \filter_configure();
                      return true;
                }
            }
            return false;
        }
        return false;
    }

    public static function show() {

        // Include pfSense files
        require_once('/etc/inc/config.inc');
        require_once('/etc/inc/filter.inc');

        // Make the config variable accessible
        global $config;

        \Base\String::print_pre($config);

        if (isset($config['ovpn_forward'])) {
            return $config['ovpn_forward']['hosts'];
        }


        return false;

    }

}