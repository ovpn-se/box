<?php
/**
 * Created by PhpStorm.
 * User: davidwibergh
 * Date: 15-04-22
 * Time: 13:44
 */

namespace OpenVPN;


class OpenVPN {

    public $config;

    public function __construct()
    {

        $file    = new \Shell\File();
        $content = $file->read('config.json');
        $config = json_decode($content);

        // Verify that we could read the contents
        if(!$content || !$config) {
            \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
            return false;
        }

        // Check if file location for the client configuration is added
        if(empty($config->files->auth)) {
            //error_log('fel config.');
            return false;
        }

        $this->config = $config->files->auth;

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
            error_log('går ej att exekvera');
            return false;
        }

        // Verify that configuration file exists
        if($command == "start") {
            if(!file_exists($this->config)) {
                error_log('finns ej: ' . $this->config);
                return false;
            }
        }

        // Verify that command is valid
        if(!in_array($command, array('start', 'stop', 'restart'))) {
            error_log( 'felaktigt kommando');
            return false;
        }

        // Execute the command
        if($command == "start") {
            $execute = $bash . ' start --config ' . $this->config;
        } elseif($command == "start") {
            $execute = $bash . ' restart --config ' . $this->config;
        } else {
            $execute = $bash . ' stop';
        }
        exec ($execute, $return, $return_var);

        // Verify response
        if($return_var != "0") {
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