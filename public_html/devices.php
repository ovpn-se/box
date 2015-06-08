<?php
require('../essentials.php');

// Verify that user credentials have been set
if(!\Base\User::getCredentials()) {
    header("Location: /login/");
    die();
}

// Fetch dhcp leases
$dhcp = \Network\IP::getDHCPleases();

// Fetch static IP addresses
$static = \Network\IP::getStaticAddresses();

// Fetch hosts that bypass VPN
$bypass = \Network\BypassVPN::get();


$data = array(
    'page' => 'devices',
    'title' => 'Enheter'
);
require('./assets/template/top.php');
?>

<h4 class="text-center">Statiska IP-adresser</h4>


    <div class="<?php if($static) { echo 'hidden'; } ?> alert alert-info" role="alert">Det verkar som att du inte har några enheter som har statiska IP-adresser. Om du ändrar så att enheterna har statiska IP-adresser kan du ändra så att alla enheter exempelvis inte går igenom OVPN.<br /><br />Läs i <a href="https://www.ovpn.se/faq/ovpnbox/">FAQ</a> hur du ändrar så att dina enheter har en statisk IP-adress istället.</div>

    <table class="<?php if(!$static) { echo 'hidden'; } ?> staticips table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Namn</th>
            <th>IP</th>
            <th>MAC-adress</th>
            <th>Status</th>
            <th>VPN</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if($static) {
            $x = 1;
            foreach($static as $entry) {

                if(\Network\Ping::isUp($entry['ip'])) {
                    $online = 'Ansluten';
                } else {
                    $online = '-';
                }

                if(empty($entry['hostname'])) {
                    $hostname = '<i>Ej angivet</i>';
                } else {
                    $hostname = $entry['hostname'];
                }

                if(isset($bypass[md5($entry['ip'])])) {
                    $bypassEntry = '<a href="javascript:void(0);" class="remove_bypass" title="Klicka för att skydda enheten bakom OVPN" data-ip="' . $entry['ip'] . '"><i class="fa fa-shield"></i></a>';
                } else {
                    $bypassEntry = '<a href="javascript:void(0);" class="activate_bypass" title="Klicka för att pausa enhetens skydd" data-ip="' . $entry['ip'] . '"><i class="fa fa-pause"></i></a>';
                }

                echo
                    '<tr>' .
                    '<th scope="row">' . $x . '</th>' .
                    '<td>' . $hostname . '</td>' .
                    '<td>' . $entry['ip'] . '</td>' .
                    '<td>' . $entry['mac'] . '</td>' .
                    '<td>' . $online . '</td>' .
                    '<td>' . $bypassEntry . '</td>' .
                    '</tr>';

                $x++;
            }
            unset($x);
        }
        ?>
        </tbody>
    </table>

    <h4 class="text-center">Dynamiska IP-adresser</h4>

    <div class="<?php if($dhcp) { echo 'hidden'; } ?> alert alert-info" role="alert">Det verkar som att du inte har några enheter som har dynamiska IP-adresser. Om du inte ändrat så att alla enheter har statiska IP-adresser är detta väldigt konstigt och beror troligtvis på en felaktig sökväg i konfigurationsfilen.</div>

    <table class="<?php if(!$dhcp) { echo 'hidden'; } ?> dynamicips table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Namn</th>
                <th>IP</th>
                <th>MAC-adress</th>
                <th>Status</th>
                <th>Hantera</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $x = 1;
            foreach($dhcp as $entry) {

                if($entry['online'] == 'online') {
                    $online = 'Ansluten';
                } else {
                    $online = '-';
                }

                // Verify that the device isn't static as well
                if($static) {
                    // @todo check mac
                }

                if(!isset($entry['hostname'])) {
                    $hostname = '<i>Ej angivet</i>';
                } else {
                    $hostname = $entry['hostname'];
                }
                echo
                    '<tr id="' . $x . '">' .
                        '<th scope="row">' . $x . '</th>' .
                        '<td>' . $hostname . '</td>' .
                        '<td>' . $entry['ip'] . '</td>' .
                        '<td>' . $entry['mac'] . '</td>' .
                        '<td>' . $online . '</td>' .
                        '<td><a href="javascript:void(0);" class="edit_device" title="Klicka för att ge enheten en statisk IP-adress" data-online="' . $online . '" data-hostname="' . $hostname . '" data-mac="' . $entry['mac'] . '" data-rowid="' . $x . '"><i class="fa fa-plus"></i></a></td>' .
                    '</tr>';

                $x++;
            }
            ?>
        </tbody>
    </table>

<?php require('./assets/template/footer.php'); ?>