<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-05-19
 * Time: 12:50
 */

namespace Shell;


class Update {

    /**
     * Fetches information in regards to the latest commit on Github
     *
     * @return array|bool
     */
    public function checkLatestCommit()
    {
        // Fetch datacenters

        try {
            $response = \Unirest\Request::get("https://api.github.com/repos/ovpn-se/box/commits");

            // Verify respons
            if($response->code != 200) {
                return false;
            }

            $date = new \DateTime(
                $response->body[0]->commit->committer->date,
                new \DateTimeZone('Europe/Stockholm')
            );

            return array(
                'commit' => array(
                    'full' => $response->body[0]->sha,
                    'short' => substr($response->body[0]->sha, 0, 10),
                ),
                'date' => $date->getTimestamp()
            );
        } catch(\Exception $ex) {
            return false;
        }


    }

    /**
     * Check whether or not an update is available
     *
     * @return array|bool
     */
    public function checkAvailableUpdate()
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
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message('Misslyckades att läsa config.json eller så var filen i ett felaktigt format');
            return false;
        }

        // Look at the current commit to see if we should update
        if(!isset($OVPNconfig->gui)) {
            \Base\Log::message('GUI-parametern fanns inte i config-filen', 'info');
            $update = true;
        } else {

            if($OVPNconfig->gui->commit->full != $release['commit']['full']) {
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

        return $release;
    }

    /**
     * Updates the current GUI for OVPNbox and writes the changes to config.json.
     */
    public function execute()
    {

        // Check if we should update
        $release = $this->checkLatestCommit();

        // Verify that we retrieved the latest commit
        if(!$release) {
            return false;
        }

        // Load the configuration file to check which version we have
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Execute update script
        $update = shell_exec('/opt/ovpn/sbin/update-from-master');
        $this->postUpdate();
        \Base\Log::message('Update of OVPN was executed. Current version: \'' . $release . '\'', 'info');
        \Base\Log::message('Output of update script:  ' . $update, 'info');

        // Set current version
        $OVPNconfig->gui = $release;

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            return false;
        }

        return true;

    }

    public function updatePfsenseConfig($config)
    {

    }

    /**
     * Placerholder function that runs certain commands when necessary after an update
     *
     */
    public function postUpdate()
    {

    }

} 