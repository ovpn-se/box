<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-18
 * Time: 13:52
 */

namespace Network;


class Ping {

    /**
     * Checks whether an IP is connected through pinging.
     *
     * @param $ip
     */
    public static function isUp($ip)
    {

        // Create and execute bash script that returns the amount of hops
        $query = '/sbin/ping -c 1 -t 1 ' . escapeshellarg($ip) . ' 2>/dev/null | tail -2 | awk \'NR%2==1\'';
        $response = shell_exec($query);

        if(is_array($response)) {
            $response = $response[0];
        }

        $response = trim($response);

        if($response == '1 packets transmitted, 1 packets received, 0.0% packet loss') {
            return true;
        } else {
            return false;
        }


    }

} 