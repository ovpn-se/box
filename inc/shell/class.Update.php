<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-19
 * Time: 12:50
 */

namespace Shell;


class Update {

    public function checkLatestCommit()
    {
        // Fetch datacenters
        $response = \Unirest\Request::get("https://api.github.com/repos/ovpn-se/box/commits");

        // Verify respons
        if($response->code != 200) {
            return false;
        }

        $date = new \DateTime(
            $response->body->commit->committer->date,
            new \DateTimeZone('Europe/Stockholm')
        );

        return array(
            'commit' => array(
                'full' => $response->body->sha,
                'short' => substr($response->body->sha, 0, 10),
            ),
            'date' => $date->getTimestamp()
        );

    }

    /**
     * Updates the current GUI for OVPNbox and writes the changes to config.json.
     */
    public function execute()
    {

        // Fetch the latest commit
        $release = $this->checkLatestCommit();

        // Verify that we retrieved the latest commit
        if(!$release) {
            return false;
        }

        // Load the configuration file to check which version we have
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Look at the current commit to see if we should update
        if(!isset($config->gui)) {
            $update = true;
        } else {

            if($config->gui->commit->full != $release->commit->full) {
                $update = true;
            } else {
                $update = false;
            }
        }

        // Check if we should update the GUI
        if(!$update) {

            // No update required since user has the latest version
            return false;

        }

        // Execute update script
        // @todo insert update script.
        $success = true;

        if($success) {

            // Set server IP
            $config->gui = $release;

            // Save credentials and session data in the config file
            $write = $file->write(array('file' => 'config.json', 'content' => json_encode($config,JSON_PRETTY_PRINT)));

            // Verify that the file write was successful
            if(!$write) {
                \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
                return false;
            }

            return true;
        }

        return false;
    }

} 