<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-20
 * Time: 12:56
 */

namespace Network;


class Port {

    /**
     * Purges all port forwards
     *
     * @return bool
     */
    public static function purge() {
        return purgePortForwards();
    }

    /**
     * Adds a port forward for host
     *
     * @param $ip
     * @param $port
     * @param $proto
     * @return bool
     */
    public static function activate($ip,$port,$proto) {
        return activatePortForwading($ip, $port, $proto);
    }

    /**
     * Removes a port forward for host
     *
     * @param $ip
     * @param $port
     * @param $proto
     * @return bool
     */
    public static function deactivate($ip,$port,$proto) {
        return deactivatePortForwarding($ip,$port,$proto);
    }

    /**
     * Shows all current port forwards
     *
     * @return array|bool
     */
    public static function get() {
        return showPortForwards();
    }

}