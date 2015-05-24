<?php
require('../essentials.php');

// Verify that user credentials have been set
if(!\Base\User::getCredentials()) {
    header("Location: /login/");
    die();
}


// Fetch static IP addresses
$static = \Network\IP::getStaticAddresses();


$data = array(
    'page' => 'ports'
);
require('./assets/template/top.php');
?>

<h4 class="text-center">Portvidarebefordran</h4>


    <table class="table table-striped">
        <thead>
        <tr>
            <th>#</th>
            <th>Enhet</th>
            <th>Port</th>
            <th>Typ</th>
            <th>Hantera</th>
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


<h4 class="text-center">Ny befordran</h4>
<form class="form-inline text-center" role="form" id="port_form">
    <div class="form-group">
        <label for="port_protocol" class="control-label"></label>
        <div class="col-sm-6">
            <select class="form-control" name="device" id="device">
                <?php
                if($static) {
                    foreach($static as $entry) {
                        if(empty($entry['hostname'])) {
                            $hostname = '<i>Ej angivet</i>';
                        } else {
                            $hostname = $entry['hostname'];
                        }
                        echo '<option value="' . $entry['ip'] . '">' . $entry['ip'] . ' / ' . $hostname . '</option>';
                    }
                } else {
                    echo '<option value="0">Endast enheter med statisk IP kan utnyttja portvidarebefordran</option>';
                }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="port_number" class="control-label"></label>
        <div class="col-sm-6">
            <input type="text" class="form-control" id="port_number" name="port_number" placeholder="Port" />
        </div>
    </div>
    <div class="form-group" style="margin-top:15px;">
        <label for="port_protocol" class="control-label"></label>
        <div class="col-sm-6">
            <select class="form-control" name="port_protocol" id="port_protocol">
                <option value="TCP">TCP</option>
                <option value="UDP">UDP</option>
                <option value="TCP/UDP">TCP/UDP</option>
            </select>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-6">
            <button type="submit" class="btn btn-primary" style="margin-top:15px;"><?php echo _('Öppna port'); ?></button>
        </div>
    </div>
</form>




<?php require('./assets/template/footer.php'); ?>