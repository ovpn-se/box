<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-24
 * Time: 10:39
 */

namespace API;


use Slim\Slim;

class Update {

    public function get()
    {
        $app = Slim::getInstance();

        // Check whether an update is available
        $update = new \Shell\Update();
        $release = $update->checkAvailableUpdate();

        // Verify the return data
        if(!$release) {

            // No update is available
            $app->halt(304);
        }

        // Return success
        $app->response->status(200);
        $app->response->body(
            json_encode($release)
        );
        $app->stop();

    }

    public function execute()
    {

        $app = Slim::getInstance();

        // Update
        $update  = new \Shell\Update();
        $success = $update->execute();

        // Verify the return data
        if(!$success) {

            // An error occurred
            $app->halt(500, json_encode(array('status' => false, 'error' => 'Ett tekniskt fel har inträffat under uppdateringen.')));
        }

        // Return success
        $app->response->status(200);
        $app->response->body(
            json_encode(
                array(
                    'status' => true
                )
            )
        );
        $app->stop();
    }
} 