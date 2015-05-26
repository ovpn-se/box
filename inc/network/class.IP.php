<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-22
 * Time: 20:36
 */

namespace Network;


class IP {

    /**
     * Fetch external IP via websites
     *
     * @return bool
     */
    public static function external()
    {
        // Fetch datacenters
        $response = \Unirest\Request::get("https://www.ovpn.se/v1/api/ip");

        // Verify respons
        if($response->code != 200) {

            $response = \Unirest\Request::get("http://canihazip.com/s");

            if($response->code != 200) {
                return false;
            } else {
                $ip = $response->raw_body;
            }
        } else {
            $ip = $response->body->ip;
        }

        return $ip;
    }

    /**
     * Fetches internal IP from OpenVPN interface
     */
    public static function internal()
    {
        // Fetch the users selected interfaces
        $selectedInterfaces = \Base\User::getUserInterfaces();

        // Verify that user has selected interfaces
        if(!$selectedInterfaces) {
            return false;
        }

        // Fetch the existing interfaces on the computer
        $adapter    = new Adapter();
        $existingInterfaces = $adapter->get();

        // Loop through all interfaces
        foreach($existingInterfaces as $interface) {

            if($interface['interface'] == $selectedInterfaces->openvpn) {

                if(!empty($interface['ip_address'])) {
                    return $interface['ip_address'];
                } else {
                    return false;
                }
            }
        }

        return false;

    }

    public static function normal()
    {

        // Fetch the users selected interfaces
        $selectedInterfaces = \Base\User::getUserInterfaces();

        // Verify that user has selected interfaces
        if(!$selectedInterfaces) {
            return false;
        }

        // Fetch the existing interfaces on the computer
        $adapter    = new Adapter();
        $existingInterfaces = $adapter->get();

        // Loop through all interfaces
        foreach($existingInterfaces as $interface) {

            if($interface['interface'] == $selectedInterfaces->wan) {

                if(!empty($interface['ip_address'])) {
                    return $interface['ip_address'];
                } else {
                    return false;
                }
            }
        }

        return false;

    }

    /**
     * Fetch the server IP
     */
    public static function server()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Update credentials and session
        if(!isset($OVPNconfig->server)) {
            return false;
        }

        return $OVPNconfig->server;
    }

    public static function getStaticAddresses()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Verify that the pfsense path is added
        if(empty($OVPNconfig->files->pfsense)){
            return false;
        }

        // Load the pfsense configuration file
        $xml = new \SimpleXMLElement(
            file_get_contents($OVPNconfig->files->pfsense)
        );

        // Verify that we have any static mappings
        if(empty($xml->dhcpd->lan->staticmap)) {
            return false;
        }

        // Return array
        $static = array();

        // Loop through all entries
        foreach($xml->dhcpd->lan->staticmap as $mapping) {

            // If cid exists, use that as identifier
            if(empty($mapping->cid)) {
                $hostname = $mapping->hostname;
            } else {
                $hostname = $mapping->cid;
            }

            // Add to array
            $static[md5((string)$mapping->ipaddr)] = array(
                'mac'      => (string)$mapping->mac,
                'ip'       => (string)$mapping->ipaddr,
                'hostname' => (string)$hostname
            );
        }

        return $static;
    }

    /**
     * Fetch all DHCP leases
     *
     * @return array|bool
     */
    public static function getDHCPleases()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(empty($OVPNconfig->files->dhcp)){
            return false;
        }

        $awk = "/usr/bin/awk";
        /* this pattern sticks comments into a single array item */
        $cleanpattern = "'{ gsub(\"#.*\", \"\");} { gsub(\";\", \"\"); print;}'";
        /* We then split the leases file by } */
        $splitpattern = "'BEGIN { RS=\"}\";} {for (i=1; i<=NF; i++) printf \"%s \", \$i; printf \"}\\n\";}'";

        /* stuff the leases file in a proper format into a array by line */
        exec("/bin/cat {$OVPNconfig->files->dhcp} | {$awk} {$cleanpattern} | {$awk} {$splitpattern}", $leases_content);
        $leases_count = count($leases_content);
        exec("/usr/sbin/arp -an", $rawdata);

        $arpdata_ip = array();
        $arpdata_mac = array();
        foreach ($rawdata as $line) {
            $elements = explode(' ',$line);
            if ($elements[3] != "(incomplete)") {
                $arpent = array();
                $arpdata_ip[] = trim(str_replace(array('(',')'),'',$elements[1]));
                $arpdata_mac[] = strtolower(trim($elements[3]));
            }
        }
        unset($rawdata);
        $pools = array();
        $leases = array();
        $i = 0;
        $l = 0;
        $p = 0;


        // Put everything together again
        foreach($leases_content as $lease) {
            /* split the line by space */
            $data = explode(" ", $lease);
            /* walk the fields */
            $f = 0;
            $fcount = count($data);
            /* with less than 20 fields there is nothing useful */
            if($fcount < 20) {
                $i++;
                continue;
            }
            while($f < $fcount) {
                switch($data[$f]) {
                    case "failover":
                        $pools[$p]['name'] = trim($data[$f+2], '"');
                        $pools[$p]['mystate'] = $data[$f+7];
                        $pools[$p]['peerstate'] = $data[$f+14];
                        $pools[$p]['mydate'] = $data[$f+10];
                        $pools[$p]['mydate'] .= " " . $data[$f+11];
                        $pools[$p]['peerdate'] = $data[$f+17];
                        $pools[$p]['peerdate'] .= " " . $data[$f+18];
                        $p++;
                        $i++;
                        continue 3;
                    case "lease":
                        $leases[$l]['ip'] = $data[$f+1];
                        $leases[$l]['type'] = "dynamic";
                        $f = $f+2;
                        break;
                    case "starts":
                        $leases[$l]['start'] = $data[$f+2];
                        $leases[$l]['start'] .= " " . $data[$f+3];
                        $f = $f+3;
                        break;
                    case "ends":
                        if ($data[$f+1] == "never") {
                            // Quote from dhcpd.leases(5) man page:
                            // If a lease will never expire, date is never instead of an actual date.
                            $leases[$l]['end'] = gettext("Never");
                            $f = $f+1;
                        } else {
                            $leases[$l]['end'] = $data[$f+2];
                            $leases[$l]['end'] .= " " . $data[$f+3];
                            $f = $f+3;
                        }
                        break;
                    case "tstp":
                        $f = $f+3;
                        break;
                    case "tsfp":
                        $f = $f+3;
                        break;
                    case "atsfp":
                        $f = $f+3;
                        break;
                    case "cltt":
                        $f = $f+3;
                        break;
                    case "binding":
                        switch($data[$f+2]) {
                            case "active":
                                $leases[$l]['act'] = "active";
                                break;
                            case "free":
                                $leases[$l]['act'] = "expired";
                                $leases[$l]['online'] = "offline";
                                break;
                            case "backup":
                                $leases[$l]['act'] = "reserved";
                                $leases[$l]['online'] = "offline";
                                break;
                        }
                        $f = $f+1;
                        break;
                    case "next":
                        /* skip the next binding statement */
                        $f = $f+3;
                        break;
                    case "rewind":
                        /* skip the rewind binding statement */
                        $f = $f+3;
                        break;
                    case "hardware":
                        $leases[$l]['mac'] = $data[$f+2];
                        /* check if it's online and the lease is active */
                        if (in_array($leases[$l]['ip'], $arpdata_ip)) {
                            $leases[$l]['online'] = 'online';
                        } else {
                            $leases[$l]['online'] = 'offline';
                        }
                        $f = $f+2;
                        break;
                    case "client-hostname":
                        if($data[$f+1] <> "") {
                            $leases[$l]['hostname'] = preg_replace('/"/','',$data[$f+1]);
                        } else {
                            $hostname = gethostbyaddr($leases[$l]['ip']);
                            if($hostname <> "") {
                                $leases[$l]['hostname'] = $hostname;
                            }
                        }
                        $f = $f+1;
                        break;
                    case "uid":
                        $f = $f+1;
                        break;
                }
                $f++;
            }
            $l++;
            $i++;
            /* slowly chisel away at the source array */
            array_shift($leases_content);
        }

        /* remove duplicate items by mac address */
        if(count($leases) > 0) {
            $leases = \Base\String::removeDuplicate($leases,"ip");
        } else {
            return false;
        }

        // Sort the array based on IP
        foreach ($leases as $key => $row) {

            $explode       = explode(".", $row['ip']);
            $ipData[$key]  = $explode[count($explode)-1];
        }

        array_multisort($ipData, SORT_ASC, $leases);

        return $leases;
    }
} 