<?php
require('../essentials.php');

// Verify that user credentials have been set
if(!\Base\User::getCredentials()) {
    header("Location: /login/");
    die();
}


// Fetch all ports
$ports = \Network\Port::get();

// Fetch static IP addresses
$static = \Network\IP::getStaticAddresses();


$data = array(
    'page' => 'ports',
    'title' => 'Portar'
);
require('./assets/template/top.php');
?>

<h4 class="text-center">Portvidarebefordran</h4>

    <div class="alert alert-info ports-display <?php if($ports) { echo 'hidden'; } ?>" role="alert">Inga portar är vidarebefordrade.</div>

    <table class="table table-striped <?php if(!$ports) { echo 'hidden'; } ?>">
        <thead>
        <tr>
            <th>Enhet</th>
            <th>Port</th>
            <th>Typ</th>
            <th>Hantera</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $x = 1;

        if($ports) {

            foreach($ports as $host) {

                foreach($host as $entry) {

                    if($entry['type'] == "tcp") {
                        $type = "TCP";
                    } else if($entry['type'] == "udp") {
                        $type = "UDP";
                    } else if($entry['type'] == "both") {
                        $type = "TCP/UDP";
                    } else {
                        $type = "";
                    }

                    if(empty($static[md5($entry['ip'])]['hostname'])) {
                        $hostname = '<i>Ej angivet</i>';
                    } else {
                        $hostname = $static[md5($entry['ip'])]['hostname'];
                    }

                    echo
                        '<tr id="port-' . $x . '">' .
                        '<td>' . $entry['ip'] . ' / ' . $hostname . '</td>' .
                        '<td>' . $entry['port'] . '</td>' .
                        '<td>' . $type . '</td>' .
                        '<td><a href="javascript:void(0);" class="delete_port" title="Ta bort vidarebefordran" data-ip="' . $entry['ip'] . '" data-port="' . $entry['port'] . '" data-type="' . $entry['type'] . '" data-portid="' . $x . '"><i class="fa fa-trash"></i></a></td>' .
                        '</tr>';

                    $x++;
                 }
            }
            unset($x);
        }
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
                <option value="tcp">TCP</option>
                <option value="udp">UDP</option>
                <option value="both">TCP/UDP</option>
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