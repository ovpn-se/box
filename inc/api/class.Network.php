<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-26
 * Time: 20:32
 */

namespace API;


use Slim\Slim;

class Network {

    public function createPortforward()
    {

        $app = Slim::getInstance();

        // Fetch variables
        $ip   = $app->request->post('ip');
        $port = $app->request->post('port');
        $type = $app->request->post('type');


        // Verify that required variables exist
        if(is_null($ip) || is_null($port) || is_null($type)) {
            \Base\Log::message(_('Nödvändiga parametrar skickades inte med i API-anropet (ip, port, type)'));
            $app->halt(400, json_encode(array('status' => false, 'error' => _('Nödvändiga parametrar saknas.'))));
        }

        // Attempt to execute the request
        $response = activatePortForwading($ip, $port, $type);

        // Handle response
        if(!$response) {

            // API request failed for some reason
            \Base\Log::message(_('Misslyckades att vidarebefordra port för enheten'));
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Misslyckades att vidarebefordra port för enheten')));
        }

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true)));
        $app->stop();

    }

    public function deletePortforward()
    {

        $app = Slim::getInstance();

        // Fetch variables
        $ip   = $app->request->delete('ip');
        $port = $app->request->delete('port');
        $type = $app->request->delete('type');


        // Verify that required variables exist
        if(is_null($ip) || is_null($port) || is_null($type)) {
            \Base\Log::message(_('Nödvändiga parametrar skickades inte med i API-anropet (ip, port, type)'));
            $app->halt(400, json_encode(array('status' => false, 'error' => _('Nödvändiga parametrar saknas.'))));
        }

        // Attempt to execute the request
        $response = \Network\Port::deactivate($ip, $port, $type);

        // Handle response
        if(!$response) {

            // Request failed for some reason
            \Base\Log::message(_('Misslyckades att ta bort vidarebefordring av port för enheten'));
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Misslyckades att ta bort vidarebefordring av port för enheten')));
        }

        // Return success
        $app->response->status(200);
        $app->response->body(json_encode(array('status' => true)));
        $app->stop();

    }
} 