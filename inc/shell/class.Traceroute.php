<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-19
 * Time: 15:40
 */

namespace Shell;


class Traceroute {

    public function get($ip)
    {
        // Fetch user interfaces
        $interfaces = \Base\User::getUserInterfaces();

        // Verify that we retrieved correct data
        if(!$interfaces) {
            return false;
        }

        // Create and execute bash script that returns the amount of hops
        $query = '/usr/sbin/traceroute -i ' . escapeshellarg($interfaces->wan) . ' -w 3 -q 1 -m 16 -P icmp ' . escapeshellarg($ip) . ' 2>/dev/null | tail -1 | awk \'{print $1}\'';
        $hops = shell_exec($query);

        if(is_array($hops)) {
            $hops = $hops[0];
        }

        $hops = trim($hops);

        // Verify that a number was returned to us.
        if(!is_numeric($hops)) {
            return false;
        }

        // Return the number of hops.
        return $hops;
    }
} 