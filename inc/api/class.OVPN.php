<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-19
 * Time: 20:44
 */

namespace API;


use Slim\Slim;

class OVPN {

    /**
     * Finds the datacenter with the best route for the client.
     *
     */
    public function getBestDatacenter()
    {

        $app = Slim::getInstance();

        // Fetch datacenters
        $response = \Unirest\Request::get("https://www.ovpn.se/v1/api/client/datacenters");

        // Verify respons
        if($response->code != 200) {
            \Base\Log::message($response->body->error);
            $app->halt($response->code, json_encode(array('status' => false, 'error' => $response->body->error)));
        }

        $datacenters = array();

        // Loop through all datacenters
        foreach($response->body as $datacenter) {

            $traceroute = new \Shell\Traceroute();
            $hops       = $traceroute->get($datacenter->ip);

            if($hops) {
                $datacenter->hops = $hops;
            } else {
                $datacenter->hops = 99;
            }

            $datacenters[] = $datacenter;
        }

        // Sort the array with ascending so we get the datacenter with the lowest amount of hops
        $sort = array();
        foreach ($datacenters as $key => $row) {
            $sort[$key]  = $row->hops;
        }

        array_multisort($sort, SORT_ASC, $datacenters);

        $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));


        // Save the best datacenter in config.json
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig  = json_decode($content);

        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Update credentials and session
        $OVPNconfig->datacenter = array(
            'location' => $datacenters[0]->slug,
            'timestamp' => $date->getTimestamp()
        );

        // Save credentials and session data in the config file
        $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true, 'datacenter' => $datacenters[0]->slug)));
        $app->stop();

    }

    /**
     * Check if the box is connected to OVPN
     */
    public function isConnected()
    {

        $app = Slim::getInstance();

        $killswitch = $app->request->get('killswitch');

        // Check if connected
        $connected = \Network\Adapter::isConnectedToOVPN();

        if(!$connected) {
            $app->halt(502);
        }

        if($killswitch == 'true') {
            $activateKillswitch = true;
        } else {
            $activateKillswitch = false;
        }

        var_dump($activateKillswitch);

        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true)));
        $app->stop();
    }

    /**
     * Disconnect from OVPN
     */
    public function disconnect()
    {

        $app = Slim::getInstance();

        // Start OpenVPN
        $openvpn = new \OpenVPN\OpenVPN();
        $disconnect = $openvpn->stop();

        // Verify
        if(!$disconnect) {
            \Base\Log::message(_('Misslyckades att stoppa OpenVPN.'));
            $app->halt(500, json_encode(array('error' => _('Misslyckades att stoppa OpenVPN.'))));
        }

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig  = json_decode($content);

        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Update credentials and session
        unset($OVPNconfig->server);

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true)));
        $app->stop();
    }

    /**
     * Connect to OVPN
     *
     * @return bool
     */
    public function connect()
    {

        $app = Slim::getInstance();

        // Hämta variabler.
        $ip           = $app->request->post('ip');
        $addon        = $app->request->post('addon');

        // Verifiera att parametrarna är angivna.
        if(is_null($ip) || is_null($addon)) {
            \Base\Log::message(_('Alla parametrar är inte angivna. Krävs: ip, addon, killswitch'));
            $app->halt(400, json_encode(array('error' => _('Alla parametrar är inte angivna.'))));
        }

        // Update the OpenVPN configuration file
        $client = new \OpenVPN\ClientConfig();
        $OVPNconfig = $client->generate(
            $ip,
            $addon
        );

        // Verify that the configuration file was updated.
        if(!$OVPNconfig) {
            \Base\Log::message(_('Konfigurationsfilen till OpenVPN uppdaterades inte. Kolla skrivbehörigheter & så att rätt tilläggstjänst las till.'));
            $app->halt(400, json_encode(array('error' => _('Konfigurationsfilen till OpenVPN uppdaterades inte. Kolla skrivbehörigheter & så att rätt tilläggstjänst las till.'))));
        }

        // Start OpenVPN
        $openvpn = new \OpenVPN\OpenVPN();
        $connect = $openvpn->start();

        // Verify
        if(!$connect) {
            \Base\Log::message(_('Misslyckades att starta OpenVPN.'));
            $app->halt(500, json_encode(array('error' => _('Misslyckades att starta OpenVPN.'))));
        }

        // The API request succeeded.
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig  = json_decode($content);

        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Set server IP
        $OVPNconfig->server = $ip;

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true)));
        $app->stop();
    }

    /**
     * Finds the server in a datacenter that has the lowest bandwidth usage.
     *
     * @param $datacenter
     */
    public function getBestServer($datacenter)
    {

        $app = Slim::getInstance();

        // Fetch datacenters
        $response = \Unirest\Request::get("https://www.ovpn.se/v1/api/client/servers/" . $datacenter);

        // Verify respons
        if($response->code != 200) {
            \Base\Log::message($response->body->error);
            $app->halt($response->code, json_encode(array('status' => false, 'error' => $response->body->error)));
        }

        $servers = $response->body;

        // Sort the array with ascending so we get the datacenter with the lowest amount of hops
        $sort = array();
        foreach ($servers as $key => $row) {

            if($row->ptr == "vpn05.prd.kista.ovpn.se") {
                $sort[$key]  = 100;
            } else {
                $sort[$key]  = $row->currentLoad->bandwidth;
            }

        }

        array_multisort($sort, SORT_ASC, $servers);

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true, 'server' => $servers[0])));
        $app->stop();
    }

} 