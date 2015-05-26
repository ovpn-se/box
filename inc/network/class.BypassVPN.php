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
        return purgeVPNBypass();
    }

    /**
     * Activates bypass for a specific IP
     *
     * @param $ip
     * @return bool
     */
    public static function activate($ip) {
        return activateVPNBypass($ip);
    }

    /**
     * Deactivates bypass for a specific IP
     *
     * @param $ip
     * @return bool
     */
    public static function deactivate($ip) {
        return deactivateVPNBypass($ip);
    }

    /**
     * Returns a list of all the IPs that currently bypass the VPN connection
     *
     * @return bool
     */
    public static function get() {
        return showVPNBypassHosts();
    }

} 