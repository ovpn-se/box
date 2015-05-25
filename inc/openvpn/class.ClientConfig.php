<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-21
 * Time: 20:52
 */

namespace OpenVPN;


use Base\String;

class ClientConfig {

    public function generate($ip, $type)
    {
        $config_types = array(
            'normal' => array(
                'ports' => array(
                    1194,
                    1195
                ),
            ),
            'filtering' => array(
                'ports' => array(
                    1198,
                    1199
                )
            ),
            'public-ipv4' => array(
                'ports' => array(
                    1196,
                    1197
                )
            ),
            'multihop' => array(
                'ports' => array(
                    1201,
                    1202
                )
            )
        );

        if(!isset($config_types[$type])) {
            return false;
        }

        $externalIP = \Network\IP::external();

        $clientconfig = <<<EOT
dev ovpnc1
verb 3
dev-type tun
tun-ipv6
dev-node /dev/tun1
writepid /var/run/openvpn_client1.pid
script-security 3
daemon
keepalive 10 60
ping-timer-rem
persist-tun
persist-key
proto udp
cipher AES-256-CBC
auth SHA1
up /usr/local/sbin/ovpn-linkup
down /usr/local/sbin/ovpn-linkdown
local {$externalIP}
log /tmp/openvpn.log
engine cryptodev
tls-client
client
lport 0
management /var/etc/openvpn/client1.sock unix
remote {$ip} {$config_types[$type]['ports'][0]}
remote {$ip} {$config_types[$type]['ports'][1]}
remote-random
auth-user-pass /var/etc/openvpn/client1.up
ca /var/etc/openvpn/client1.ca
comp-lzo adaptive
resolv-retry infinite
remote-cert-tls server
reneg-sec 432000
persist-key
persist-tun
key-direction 1
mute-replay-warnings
replay-window 256
<tls-auth>
-----BEGIN OpenVPN Static key V1-----
81782767e4d59c4464cc5d1896f1cf60
15017d53ac62e2e3b94b889e00b2c69d
dc01944fe1c6d895b4d80540502eb719
10b8d785c9efa9e3182343532adffe1c
fbb7bb6eae39c502da2748edf0fb89b8
a20b0a1085cc1f06135037881bc0c4ad
8f2c0f4f72d2ab466fb54af3d8264c5f
ddeb0f21aa0ca41863678f5fc4c44de4
ca0926b36dfddc42c6f2fabd1694bdc8
215b2d223b9c21dc6734c2c778093187
afb8c33403b228b9af68b540c284f6d1
83bcc88bd41d47bd717996e499ce1cbb
fa768a9723c19c58314c4d19cfed82e5
43ee92e73d38ad26d4fbec231c0f9f3b
30773a5c87792e9bc7c34e8d7611002e
bedd044e48a0f1f96527bfdcc940aa09
-----END OpenVPN Static key V1-----
</tls-auth>
EOT;

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Check if file location for the client configuration is added
        if(empty($OVPNconfig->files->auth)) {
            return false;
        }

        saveOpenVPNConfig($ip, $config_types[$type]['ports'][0]);

        /*$write = $file->write(
            array(
                'file' => $OVPNconfig->files->auth,
                'content' => $clientconfig
            )
        );

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            return false;
        }*/

       /* // Load the pfsense configuration file
        $xml = new \SimpleXMLElement(
            file_get_contents($OVPNconfig->files->pfsense)
        );

        $tls = '/var/etc/openvpn/ovpn-tls.key';
        if(!file_exists($tls)) {
            \Shell\System::setReadWrite();
            file_put_contents($tls, fopen("https://www.ovpn.se/download/ovpn-tls.key", 'r'));
            \Shell\System::setReadOnly();
        }

        // Loop through all entries
        $x = 0;
        foreach($xml->openvpn->{'openvpn-client'} as $mapping) {

            $xml->openvpn->{'openvpn-client'}[$x]->protocol = 'UDP';
            $xml->openvpn->{'openvpn-client'}[$x]->dev_mode = 'tun';
            $xml->openvpn->{'openvpn-client'}[$x]->interface = 'wan';
            $xml->openvpn->{'openvpn-client'}[$x]->server_addr = $ip;
            $xml->openvpn->{'openvpn-client'}[$x]->server_port = $config_types[$type]['ports'][0];
            $xml->openvpn->{'openvpn-client'}[$x]->resolve_retry = 'yes';
            $xml->openvpn->{'openvpn-client'}[$x]->proxy_authtype = 'none';
            $xml->openvpn->{'openvpn-client'}[$x]->description = 'OVPN';
            $xml->openvpn->{'openvpn-client'}[$x]->mode = 'p2p_tls';
            $xml->openvpn->{'openvpn-client'}[$x]->crypto = 'AES-256-CBC';
            $xml->openvpn->{'openvpn-client'}[$x]->digest = 'SHA1';
            $xml->openvpn->{'openvpn-client'}[$x]->engine = 'cryptodev';
            $xml->openvpn->{'openvpn-client'}[$x]->compression = 'adaptive';
            $xml->openvpn->{'openvpn-client'}[$x]->verbosity_level = '3';
            $xml->openvpn->{'openvpn-client'}[$x]->custom_options = 'remote-cert-tls server;reneg-sec 432000;persist-key;persist-tun;mute-replay-warnings;replay-window 256;tls-auth ' . $tls . ' 1;log /tmp/openvpn.log;';
            $x++;
        }

        $xml->asXML($OVPNconfig->files->pfsense);
        shell_exec('rm /tmp/config.cache');
        shell_exec('/etc/rc.openvpn');*/

        return true;

    }
} 