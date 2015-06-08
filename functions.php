<?php

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

        if(!isset($config['ovpn']['ovpn_killswitch']) || $config['ovpn']['ovpn_killswitch'] == 0) {
            $config['ovpn']['ovpn_killswitch'] = 1;
            \write_config('Disabled the killswitch', false, true);
            shell_exec('/etc/rc.filter_configure_sync');
            shell_exec('/sbin/pfctl -F state -i ' . $ovpn_wan);
        }
    } else {

        if(!isset($config['ovpn']['ovpn_killswitch']) || $config['ovpn']['ovpn_killswitch'] == 1) {
            $config['ovpn']['ovpn_killswitch'] = 0;
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

    if (isset($config['ovpn']['ovpn_bypass'])) {
        unset($config['ovpn']['ovpn_bypass']);
        \write_config('Purged all IP addresses that bypass VPN', false, true);
        shell_exec('/etc/rc.filter_configure_sync');
        shell_exec('/sbin/pfctl -F state -i ' . \get_real_interface("wan"));
    }

    return true;
}

function addStaticMapping($hostname, $mac, $ip)
{

    // Make the config variable accessible
    global $g, $config;

    $a_maps = &$config['dhcpd']['lan']['staticmap'];

    $mapent = array();
    $mapent['mac'] = $mac;
    $mapent['cid'] = '';
    $mapent['ipaddr'] = $ip;
    $mapent['hostname'] = $hostname;
    $mapent['descr'] = '';
    $mapent['arp_table_static_entry'] = false;
    $mapent['filename'] = '';
    $mapent['rootpath'] = '';
    $mapent['defaultleasetime'] = '';
    $mapent['maxleasetime'] = '';
    $mapent['gateway'] = '';
    $mapent['domain'] = '';
    $mapent['domainsearchlist'] = '';
    $mapent['ddnsdomain'] = '';
    $mapent['ddnsdomainprimary'] = '';
    $mapent['ddnsdomainkeyname'] = '';
    $mapent['ddnsdomainkey'] = '';
    $mapent['ddnsupdate'] = false;
    $mapent['tftp'] = '';
    $mapent['ldap'] = '';

    $a_maps[] = $mapent;

    staticmaps_sort('lan');

    \write_config('Added static mapping', false, true);
    \services_dhcpd_configure();

    return true;
}

function staticmaps_sort($ifgui) {
    global $g, $config;

    usort($config['dhcpd'][$ifgui]['staticmap'], "staticmapcmp");
}

function staticmapcmp($a, $b) {
    return \ipcmp($a['ipaddr'], $b['ipaddr']);
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

    if (!isset($config['ovpn']['ovpn_bypass'])) {
        $config['ovpn']['ovpn_bypass'] = array();
    }

    // Check so the IP isn't currently being bypassed.
    if(!empty($config['ovpn']['ovpn_bypass'])) {
        foreach($config['ovpn']['ovpn_bypass'] as $entry) {
            if($entry['ip'] == $ip) {
                return false;
            }
        }
    }

    // The IP isn't currently being bypassed so let's add it to the configuration file.
    $config['ovpn']['ovpn_bypass'][] = array('ip' => $ip);
    \write_config('Added IP address to bypass VPN', false, true);
    shell_exec('/etc/rc.filter_configure_sync');


    $file    = new \Shell\File();
    $content = $file->read('config.json');
    $OVPNconfig  = json_decode($content);

    if(!$content || !$OVPNconfig) {
        \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
    }

    shell_exec('/sbin/pfctl -F state -i ' . $OVPNconfig->interfaces->openvpn);

    return true;

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

    if (isset($config['ovpn']['ovpn_bypass']) && !empty($config['ovpn']['ovpn_bypass'])) {
        foreach ($config['ovpn']['ovpn_bypass'] as $key => $entry) {
            if ($entry['ip'] == $ip ) {


                unset($config['ovpn']['ovpn_bypass'][$key]);
                \write_config('Removed IP address from bypass', false, true);
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
    if (!isset($config['ovpn']['ovpn_bypass'])) {
        return false;
    }

    $retVal = array();

    foreach($config['ovpn']['ovpn_bypass'] as $entry) {

        $retVal[md5($entry['ip'])] = $entry['ip'];
    }

    return $retVal;

}