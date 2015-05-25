<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-16
 * Time: 09:50
 */

namespace Base;


class User {

    /**
     * Fetch the credentials from the configuration file
     */
    public static function getCredentials()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(!empty($OVPNconfig->credentials->username) &&
           !empty($OVPNconfig->credentials->password)
        ) {
            return $OVPNconfig->credentials;
        } else {
            return false;
        }
    }

    /**
     * Returns the session for a user
     *
     * @return bool
     */
    public static function getSession()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(isset($OVPNconfig->session)) {
            return $OVPNconfig->session;
        } else {
            return false;
        }

    }

    /**
     * Fetch content from a file.
     *
     * @return bool|string
     */
    public static function getBestDatacenter()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(empty($OVPNconfig->datacenter)) {
            return false;
        }

        // Get current timestamp
        $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));
        $ts = $date->getTimestamp();

        // Check if it's been a week since the last time we checked the route
        if(($ts-$OVPNconfig->datacenter->timestamp) >= 604800) {

            // Remove the chosen datacenter & ave file
            unset($OVPNconfig->datacenter);
            $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

            // Verify that the file write was successful
            if(!$write) {
                \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
                return false;
            }

            return false;
        }

        return $OVPNconfig->datacenter->location;
    }

    /**
     * Returns the interfaces for a user
     *
     * @return bool
     */
    public static function getUserInterfaces()
    {
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(!empty($OVPNconfig->interfaces->wan) && !empty($OVPNconfig->interfaces->lan) && !empty($OVPNconfig->interfaces->openvpn)){
            return $OVPNconfig->interfaces;
        } else {
            return false;
        }

    }

    /**
     * Logga ut användare
     */
    public static function logout()
    {

        // The API request succeeded.
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Update credentials and session
        $OVPNconfig->credentials->username = '';
        $OVPNconfig->credentials->password = '';

        if(isset($OVPNconfig->session)) {
            unset($OVPNconfig->session);
        }

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            return false;
        }

        return true;
    }

    public static function getAddons()
    {
        // Fetch session
        $session = \Base\User::getSession();

        if(!$session) {
            return false;
        }

        $addons = array();

        foreach($session->addons as $addonType => $addonData) {

            if($addonData->active) {
                $addons[$addonType] = $addonData;
            }
        }

        if(empty($addons)) {
            return false;
        }

        return $addons;
    }

} 