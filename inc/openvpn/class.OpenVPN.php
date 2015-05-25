<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-22
 * Time: 13:44
 */

namespace OpenVPN;


class OpenVPN {

    public $OVPNconfig;

    public function __construct()
    {

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
            \Base\Log::message(_('The auth file is empty.'));
            return false;
        }

        $this->OVPNconfig = $OVPNconfig->files->auth;

        return true;
    }

    /**
     * Executes a command to control OpenVPN
     *
     * @param $command
     * @return bool
     */
    public function execute($command)
    {
        $bash = '/opt/ovpn/sbin/ovpn';

        // Verify that bash script is executable
        if(!is_executable($bash)) {
            \Base\Log::message('Går ej att exekvera ' . $bash);
            return false;
        }

        // Verify that configuration file exists
        if($command == "start") {
            if(!file_exists($this->OVPNconfig)) {
                \Base\Log::message('Konfigurationsfilen finns ej: ' . $this->OVPNconfig);
                return false;
            }
        }

        // Verify that command is valid
        if(!in_array($command, array('start', 'stop', 'restart'))) {
            \Base\Log::message('Felaktigt kommando: ' . $command);
            return false;
        }

        // Execute the command
        if($command == "start") {
            $execute = $bash . ' start --config ' . $this->OVPNconfig;
        } elseif($command == "start") {
            $execute = $bash . ' restart --config ' . $this->OVPNconfig;
        } else {
            $execute = $bash . ' stop';

        }
        exec ($execute, $return, $return_var);

        // Verify response
        if($return_var != "0") {
            \Base\Log::message('OpenVPN-skript returnerade ett misslyckande: ' . $return_var);
            return false;
        }

        return true;
    }

    /**
     * Starts OpenVPN
     *
     * @return bool
     */
    public function start()
    {
        return $this->execute('start');
    }

    /**
     * Stops OpenVPN
     *
     * @return bool
     */
    public function stop()
    {
        \handleKillswitch(false);
        return $this->execute('stop');
    }

    /**
     * Restarts OpenVPN
     *
     * @return bool
     */
    public function restart()
    {
        return $this->execute('restart');
    }

} 