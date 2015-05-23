<?php
require('../essentials.php');

// Verify that user credentials have been set
$credentials = \Base\User::getCredentials();
if(!$credentials) {
    header("Location: /login/");
    die();
}

// Verify that the interfaces have been set.
$interfaces = \Base\User::getUserInterfaces();

if(!$interfaces) {
    header("Location: /setup/");
    die();
}

// Fetch session
$session = \Base\User::getSession();

// Get system data
$system  = \Shell\System::getData();

// Traffic
$traffic = array(
    'wan' => \Shell\System::getTotalTraffic($interfaces->wan),
    'lan' => \Shell\System::getTotalTraffic($interfaces->lan),
    'openvpn' => \Shell\System::getTotalTraffic($interfaces->openvpn)
);

// Verify that session was fetched.
if(!$session) {
    header("Location: /login/");
    die();
}

// Verify that user has time left on the account
if(!$session->active) {
    header("Location: /expired/");
    die();
}

// Check if user has addons
$addons = \Base\User::getAddons();

if($addons) {

    $addonData = array();
    foreach($addons as $addonKey => $data) {
        $addonData[] = \Base\String::addonAbbreviationToText($addonKey);
    }

    $addonText = implode(', ', $addonData);
} else {
    $addonText = '-';
}

$version = \Shell\System::getBoxVersion();

$data = array(
    'page' => 'system'
);
require('./assets/template/top.php');
?>
<h4>Användarinformation</h4>
    <table class="table table-striped table-width-325">
        <tbody>
        <tr>
            <th scope="row">Användarnamn</th>
            <td><?php echo \Base\String::outputCleanString($credentials->username); ?></td>
        </tr>
        <tr>
            <th scope="row">Tid kvar</th>
            <td><?php echo \Base\String::print_time(false, $session->timeleft); ?></td>
        </tr>
        <tr>
            <th scope="row">Tilläggstjänster</th>
            <td><?php echo \Base\String::outputCleanString($addonText); ?></td>
        </tr>
        </tbody>
    </table>

    <h4>Nätverksadaptrar</h4>
    <table class="table table-striped table-width-325">
        <tbody>
        <tr>
            <th scope="row">WAN</th>
            <td><?php echo \Base\String::outputCleanString($interfaces->wan . ' (' . $traffic['wan']['download'] . ' GB/' . $traffic['wan']['upload'] . ' GB)'); ?></td>
        </tr>
        <tr>
            <th scope="row">LAN</th>
            <td><?php echo \Base\String::outputCleanString($interfaces->lan . ' (' . $traffic['lan']['download'] . ' GB/' . $traffic['lan']['upload'] . ' GB)'); ?></td>
        </tr>
        <tr>
            <th scope="row">OpenVPN</th>
            <td><?php echo \Base\String::outputCleanString($interfaces->openvpn . ' (' . $traffic['openvpn']['download'] . ' GB/' . $traffic['openvpn']['upload'] . ' GB)'); ?></td>
        </tr>
        </tbody>
    </table>

    <h4>OVPNbox</h4>
    <table class="table table-striped table-width-325">
        <tbody>
        <tr>
            <th scope="row">pfSense</th>
            <td><?php echo \Base\String::outputCleanString(readfile("/etc/version") . ' (' . php_uname("m") . ')'); ?></td>
        </tr>
        <tr>
            <th scope="row">FreeBSD</th>
            <td><?php echo \Base\String::outputCleanString(php_uname("r")); ?></td>
        </tr>
        <tr>
            <th scope="row">Version</th>
            <td><?php if(!$version) { echo '-'; } else { echo \Base\String::outputCleanString($version['commit']['short']); } ?></td>
        </tr>
        <tr>
            <th scope="row">PHP</th>
            <td><?php echo \Base\String::outputCleanString(phpversion()); ?></td>
        </tr>
        <tr>
            <th scope="row">Webbserver</th>
            <td><?php echo \Base\String::outputCleanString($_SERVER['SERVER_SOFTWARE']); ?></td>
        </tr>
        </tbody>
    </table>

    <h4>System</h4>
    <table class="table table-striped table-width-325">
        <tbody>
        <tr>
            <th scope="row">OVPNbox upptid</th>
            <td><?php echo \Base\String::outputCleanString($system['uptime']); ?></td>
        </tr>
        <tr>
            <th scope="row">OpenVPN upptid</th>
            <td><?php echo \Base\String::outputCleanString($system['openvpn']); ?></td>
        </tr>
        <tr>
            <th scope="row">Belastning</th>
            <td><?php echo \Base\String::outputCleanString($system['load']); ?></td>
        </tr>
        <tr>
            <th scope="row">Processor</th>
            <td><?php echo \Base\String::outputCleanString(\Shell\System::getCPU()); ?></td>
        </tr>
        <tr>
            <th scope="row">RAM</th>
            <td><?php echo \Base\String::outputCleanString(\Shell\System::getRAM() . ' GB'); ?></td>
        </tr>
        <tr>
            <th scope="row">Temperatur</th>
            <td><?php echo \Base\String::outputCleanString(\Shell\System::getTemperature()); ?>&#176;C</td>
        </tr>
        </tbody>
    </table>



<?php require('./assets/template/footer.php'); ?>