<?php
require('../../essentials.php');

$data = array(
    'page' => 'setup'
);

$adapter   = new Network\Adapter();

if(isset($_POST['submit'])) {

    try {
        $adapter->save(
            array(
                'wan' => $_POST['wan'],
                'lan' => $_POST['lan'],
                'openvpn' => $_POST['openvpn']
            )
        );

        header("Location: /login/");
        die();
    } catch(Exception $ex) {
        $error = $ex->getMessage();
    }
}

// Fetch all available adapters

$interfaces = $adapter->get();

require('../assets/template/top.php');

if(isset($error)) {
    echo '<div class="error alert alert-danger hidden">' . $error . '</div>';
}
?>
<h3 class="text-center">Bestäm adaptrar</h3>
<div class="col-sm-6">
    <form role="form" method="post" id="interfaces">
        <div class="form-group">
            <label for="wan">WAN <a href="javascript:void(0);" data-toggle="modal" data-target="#coupon"><i class="fa fa-question-circle"></i></a></label>
            <select class="form-control" name="wan" id="wan">
                <?php
                    foreach($interfaces as $interface) {

                        // If interface re0 exists choose that as the selected value
                        if($interface['interface'] == "re0") {
                            $selected = "selected";
                        } else {
                            $selected = "";
                        }

                        // If the interface has an IP address let's include that in the display name
                        if(isset($interface['ip_address'])) {
                            $display = $interface['interface'] . ' (' . $interface['ip_address'] . ')' ;
                        } else {
                            $display = $interface['interface'];
                        }

                        // Create option
                        echo '<option value="' . $interface['interface'] . '" ' . $selected . '>' . $display . '</option>';
                    }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="lan">LAN <a href="javascript:void(0);" data-toggle="modal" data-target="#coupon"><i class="fa fa-question-circle"></i></a></label>
            <select class="form-control" name="lan" id="lan">
                <?php
                foreach($interfaces as $interface) {

                    // If interface re0 exists choose that as the selected value
                    if($interface['interface'] == "re1") {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }

                    // If the interface has an IP address let's include that in the display name
                    if(isset($interface['ip_address'])) {
                        $display = $interface['interface'] . ' (' . $interface['ip_address'] . ')' ;
                    } else {
                        $display = $interface['interface'];
                    }

                    // Create option
                    echo '<option value="' . $interface['interface'] . '" ' . $selected . '>' . $display . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="openvpn">OpenVPN <a href="javascript:void(0);" data-toggle="modal" data-target="#coupon"><i class="fa fa-question-circle"></i></a></label>
            <select class="form-control" name="openvpn" id="openvpn">
                <?php
                foreach($interfaces as $interface) {

                    // If interface re0 exists choose that as the selected value
                    if($interface['interface'] == "ovpnc1") {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }

                    // If the interface has an IP address let's include that in the display name
                    if(isset($interface['ip_address'])) {
                        $display = $interface['interface'] . ' (' . $interface['ip_address'] . ')' ;
                    } else {
                        $display = $interface['interface'];
                    }

                    // Create option
                    echo '<option value="' . $interface['interface'] . '" ' . $selected . '>' . $display . '</option>';
                }
                ?>
                <option value="create_interface">Skapa ett interface för OpenVPN</option>
            </select>
        </div>
        <div class="form-group text-center">
            <br />
            <button type="submit" name="submit" class="btn btn-primary">Gå vidare</button>
        </div>
    </form>
</div>
<div class="col-sm-6">
    <p class="text">
        Om du har köpt OVPNbox så behöver du inte ändra på några inställningar. Klicka dig bara vidare.<br /><br />
        Om du har laddat ner OVPNs gränssnitt så måste du ändra dessa inställningar så att de stämmer överrens med din dator.
    </p>
</div>
<?php require('../assets/template/footer.php'); ?>