<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-19
 * Time: 10:37
 */

namespace Shell;


class System {

    /**
     * Fetches system data
     *
     * @return array
     */
    public static function getData()
    {

        $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));

        // Fetch uptime for OVPNbox
        $rawData = trim(shell_exec('sysctl kern.boottime'));
        $explode = explode("sec = ", $rawData);
        $boot    = substr($explode[1],0,-2);


        // Fetch load
        $rawUptime = trim(shell_exec('uptime'));
        $explodeComma = explode(',', $rawUptime);
        $load = str_replace(' load averages:', '', $explodeComma[3]) . ',' . $explodeComma[4] . ',' . $explodeComma[5];

        return array(
            'uptime' => \Base\String::print_time($boot, $date->getTimestamp()),
            'load'   => $load,
            'openvpn' => System::getProcessUptime('openvpn')
        );
    }

    /**
     * Returns the current version of OVPNbox
     *
     * @return bool
     */
    public static function getBoxVersion()
    {
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(!empty($OVPNconfig->gui)){
            return $OVPNconfig->gui;
        } else {
            return false;
        }
    }

    /**
     * Fetches the processor model
     *
     * @return string
     */
    public static function getCPU()
    {
        $rawData = trim(shell_exec('sysctl hw.model'));
        $explode = explode(":", $rawData);

        return trim($explode[1]);
    }

    /**
     * Fetches the temperature
     *
     * @return string
     */
    public static function getTemperature()
    {

        $temp_out = trim(shell_exec('sysctl hw.acpi.thermal.tz0.temperature'));
        $explode = explode(":", $temp_out);
        // Remove 'C' from the end
        return rtrim($explode[1], 'C');
    }

    /**
     * Fetches installed RAM
     *
     * @return string
     */
    public static function getRAM()
    {
        $rawData = trim(shell_exec('sysctl hw.physmem'));
        $explode = explode(":", $rawData);

        return trim(round($explode[1]/1073741824));
    }

    /**
     * Fetches the total traffic volume for a specific interface
     *
     * @param $interface
     * @return array
     */
    public static function getTotalTraffic($interface)
    {
        exec("/sbin/pfctl -vvsI -i " . escapeshellarg($interface), $rawData);

        $pf_in4_pass = preg_split("/ +/ ", $rawData[3]);
        $pf_out4_pass = preg_split("/ +/", $rawData[5]);
        $pf_in6_pass = preg_split("/ +/ ", $rawData[7]);
        $pf_out6_pass = preg_split("/ +/", $rawData[9]);

        return array(
            'download' => round(($pf_in4_pass[5] + $pf_in6_pass[5])/1073741824,2),
            'upload'   => round(($pf_out4_pass[5] + $pf_out6_pass[5])/1073741824,2)
        );

    }
    /**
     * Fetches the uptime for a process
     *
     * @param $process
     * @return array|bool
     */
    public static function getProcessUptime($process) {

        $rawData = trim( shell_exec( 'ps x -e -o comm,etime' ) );
        $explode = explode("\n", $rawData);

        foreach($explode as $row) {

            // Skip all processes that don't match the requested one
            if(strpos($row, $process) === false) {
                continue;
            }

            $data = explode(" ", $row);

            foreach($data as $entry) {

                if(empty($entry)) {
                    continue;
                }

                if($entry == $process) {
                    continue;
                }

                return \Base\String::parseProcessUptime($entry);
            }

        }

        return '-';
    }

    public static function setReadOnly()
    {
        shell_exec('/etc/rc.conf_mount_ro');
    }

    public static function setReadWrite()
    {
        shell_exec('/etc/rc.conf_mount_rw');
    }

} 