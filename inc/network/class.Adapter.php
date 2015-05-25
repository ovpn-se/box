<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-16
 * Time: 07:32
 */

namespace Network;


class Adapter {

    /**
     * Fetches the current network interfaces
     *
     */
    public function get()
    {

        $ifconfig = new \Datasift\IfconfigParser\Parser\Darwin();
        $ifconfigOutput = shell_exec('/sbin/ifconfig');

        return $ifconfig->parse($ifconfigOutput);

    }

    /**
     * Save network interfaces
     *
     * @param $data
     */
    public function save($data)
    {

        // Verify that the required fields are included
        if(empty($data['wan']) || empty($data['lan']) || empty($data['openvpn'])) {
            throw new \Exception(_('Alla nödvändiga parametrar är inte angivna.'));
        }

        // Fetch the existing interfaces
        $existingInterfaces = $this->get();

        // Fetch the chosen interfaces
        $selectedInterfaces = array($data['wan'], $data['lan'], $data['openvpn']);

        // Loop through the chosen interfaces to verify they exist
        foreach($selectedInterfaces as $selectedInterface) {

            // Check if we need to create an interface
            if($selectedInterface == 'create_interface') {

                //@todo We need to create an interface for OpenVPN

            } else {

                $found = false;

                // Loop through all existing interfaces
                foreach($existingInterfaces as $existingInterface) {

                    // Check if the existing interface is the selected interface
                    if($existingInterface['interface'] == $selectedInterface) {
                        $found = true;
                        break;
                    }

                }

                // Verify that we found a correct interface
                if(!$found) {

                    // Faulty interface.
                    throw new \Exception(_('Felaktigt interface har valts.'));
                }
            }
        }

        // Fetch contents from the config file.
        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $OVPNconfig = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$OVPNconfig) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        $OVPNconfig->interfaces->wan     = $data['wan'];
        $OVPNconfig->interfaces->lan     = $data['lan'];
        $OVPNconfig->interfaces->openvpn = $data['openvpn'];

        // Save credentials and session data in the config file
        $write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

        // Verify that the file write was successful
        if(!$write) {
            \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
            return false;
        }

        return true;

    }

    /**
     * Checks whether the user is currently connected to OVPN
     */
    public static function isConnectedToOVPN()
    {

        // Fetch the users selected interfaces
        $selectedInterfaces = \Base\User::getUserInterfaces();

        // Verify that user has selected interfaces
        if(!$selectedInterfaces) {
            return false;
        }

        // Fetch the existing interfaces on the computer
        $adapter    = new Adapter();
        $existingInterfaces = $adapter->get();

        // Loop through all interfaces
        foreach($existingInterfaces as $interface) {

            if($interface['interface'] == $selectedInterfaces->openvpn) {

                if(!empty($interface['ip_address'])) {
                    return $interface['ip_address'];
                } else {
                    return false;
                }
            }
        }

        return false;
    }



} 