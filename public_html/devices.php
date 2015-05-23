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