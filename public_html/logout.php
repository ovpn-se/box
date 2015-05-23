<?php
require('../essentials.php');

// The API request succeeded.
$file    = new \Shell\File();
$content = $file->read('config.json');
$config = json_decode($content);

// Verify that we could read the contents
if(!$content || !$config) {
    \Base\Log::message(_('Misslyckades att läsa config.json eller så var filen i ett felaktigt format'));
    header("Location: /login/");
    die();
}

// Remove credentials and session
unset($config->credentials);
unset($config->session);
unset($config->server);
unset($config->datacenter);
unset($_SESSION);

// Stop OpenVPN
$openvpn    = new \OpenVPN\OpenVPN();
$openvpn->stop();

// Save credentials and session data in the config file
$write = $file->write(array('file' => 'config.json', 'content' => json_encode($config,JSON_PRETTY_PRINT)));

// Verify that the file write was successful
if(!$write) {
    \Base\Log::message(_('Misslyckades att skriva ändringar till config.json.'));
}

header("Location: /login/");
die();
