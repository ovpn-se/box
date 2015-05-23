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
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(!empty($config->credentials->username) &&
           !empty($config->credentials->password)
        ) {
            return $config->credentials;
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
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(isset($config->session)) {
            return $config->session;
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
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(empty($config->datacenter)) {
            return false;
        }

        // Get current timestamp
        $date = new \DateTime('now', new \DateTimeZone('Europe/Stockholm'));
        $ts = $date->getTimestamp();

        // Check if it's been a week since the last time we checked the route
        if(($ts-$config->datacenter->timestamp) >= 604800) {

            // Remove the chosen datacenter & ave file
            unset($config->datacenter);
            $write = $file->write(array('file' => 'config.json', 'content' => json_encode($config,JSON_PRETTY_PRINT)));

            // Verify that the file write was successful
            if(!$write) {
                \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
                return false;
            }

            return false;
        }

        return $config->datacenter->location;
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
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        if(!empty($config->interfaces->wan) && !empty($config->interfaces->lan) && !empty($config->interfaces->openvpn)){
            return $config->interfaces;
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
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Update credentials and session
        $config->credentials->username = '';
        $config->credentials->password = '';

        if(isset($config->session)) {
            unset($config->session);
        }

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($config,JSON_PRETTY_PRINT)));

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