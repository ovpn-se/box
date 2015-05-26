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
    'page' => 'devices'
);
require('./assets/template/top.php');
?>

<h4 class="text-center">Statiska IP-adresser</h4>
<?php if(!$static) { ?>

    <div class="alert alert-info" role="alert">Det verkar som att du inte har några enheter som har statiska IP-adresser. Om du ändrar så att enheterna har statiska IP-adresser kan du ändra så att alla enheter exempelvis inte går igenom OVPN.<br /><br />


    Läs i <a href="https://www.ovpn.se/faq/ovpnbox/">FAQ</a> hur du ändrar så att dina enheter har en statisk IP-adress istället.</div>

<?php } else { ?>

    <table class="table table-striped">
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
        ?>
        </tbody>
    </table>

<?php } ?>
    <h4 class="text-center">Dynamiska IP-adresser</h4>
<?php if(!$dhcp) { ?>

    <div class="alert alert-info" role="alert">Det verkar som att du inte har några enheter som har dynamiska IP-adresser. Om du inte ändrat så att alla enheter har statiska IP-adresser är detta väldigt konstigt och beror troligtvis på en felaktig sökväg i konfigurationsfilen.</div>

<?php } else { ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Namn</th>
                <th>IP</th>
                <th>MAC-adress</th>
                <th>Status</th>
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

                if(!isset($entry['hostname'])) {
                    $hostname = '<i>Ej angivet</i>';
                } else {
                    $hostname = $entry['hostname'];
                }
                echo
                    '<tr>' .
                        '<th scope="row">' . $x . '</th>' .
                        '<td>' . $hostname . '</td>' .
                        '<td>' . $entry['ip'] . '</td>' .
                        '<td>' . $entry['mac'] . '</td>' .
                        '<td>' . $online . '</td>' .
                    '</tr>';

                $x++;
            }
            ?>
        </tbody>
    </table>
<?php } ?>


<?php require('./assets/template/footer.php'); ?>