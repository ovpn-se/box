<?php
require('../essentials.php');

// The API request succeeded.
$file    = new \Shell\File();
$content = $file->read('config.json');
$OVPNconfig = json_decode($content);

// Verify that we could read the contents
if(!$content || !$OVPNconfig) {
    \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
    echo 'Ett tekniskt fel har inträffat';
    die();
}

// Remove credentials and session
if(isset($OVPNconfig->credentials)) {
    unset($OVPNconfig->credentials);
}

if(isset($OVPNconfig->session)) {
    unset($OVPNconfig->session);
}

if(isset($OVPNconfig->server)) {
    unset($OVPNconfig->server);
}

if(isset($OVPNconfig->datacenter)) {
    unset($OVPNconfig->datacenter);
}

unset($_SESSION);

// Checks whether the user is connected to OVPN or not
if(\Network\Adapter::isConnectedToOVPN()) {
    
    // Stop OpenVPN
    $openvpn    = new \OpenVPN\OpenVPN();
    $openvpn->stop();
}

// Save credentials and session data in the config file
$write = $file->write(array('file' => 'config.json', 'content' => json_encode($OVPNconfig,JSON_PRETTY_PRINT)));

// Verify that the file write was successful
if(!$write) {
    \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
}

header("Location: /login/");
die();
