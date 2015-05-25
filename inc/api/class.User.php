<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-16
 * Time: 11:07
 */

namespace API;


use Base\String;
use Slim\Slim;

class User {

    /**
     * Verify user credentials through OVPNs API
     */
    public function authenticate()
    {

        $app = Slim::getInstance();

        // Fetch variables
        $username  = $app->request->post('username');
        $password  = $app->request->post('password');

        // Verify that required variables exist
        if(is_null($username) || is_null($password)) {
            \Base\Log::message(_('Nödvändiga parametrar skickades inte med i API-anropet (username, password)'));
            $app->halt(400, json_encode(array('status' => false, 'error' => _('Nödvändiga parametrar saknas.'))));
        }

        // Execute request to OVPNs API
        $response = \Unirest\Request::post(
            "https://www.ovpn.se/v1/api/client",
            null,
            array(
                'username' => $username,
                'password' => $password
            )
        );

        // Handle response from API request
        if($response->code != 200) {

            // API request failed for some reason
            \Base\Log::message($response->body->error);
            $app->halt($response->code, json_encode(array('status' => false, 'error' => $response->body->error)));
        }

        // The API request succeeded.
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig  = json_decode($content);

        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Update credentials
        $OVPNconfig->credentials = array(
            'username' => $username,
            'password' => $password
        );

        // Update session
        $OVPNconfig->session = $response->body;

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        $credentials = <<<EOT
{$username}
{$password}
EOT;

        // Save OpenVPN credentials
        $write = $file->write(array('file' => '/var/etc/openvpn/client1.up', 'content' => $credentials));

        /*// Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva inloggningsuppgifter till OpenVPN konfiguration.'));
            $app->halt(500, json_encode(array('error' => 'Ett tekniskt fel har inträffat.')));
        }

        // Load the pfsense configuration file
        $xml = new \SimpleXMLElement(
            file_get_contents($OVPNconfig->files->pfsense)
        );

        // Loop through all entries
        $x = 0;
        foreach($xml->openvpn->{'openvpn-client'} as $mapping) {

            $xml->openvpn->{'openvpn-client'}[$x]->auth_user = $username;
            $xml->openvpn->{'openvpn-client'}[$x]->auth_pass = $password;
            $x++;
        }

        $xml->asXML($OVPNconfig->files->pfsense);
        shell_exec('rm /tmp/config.cache');
        shell_exec('/etc/rc.reload_all');*/
        \saveOpenVPNCredentials($username, $password);

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true)));
        $app->stop();

    }
} 