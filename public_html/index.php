<?php
require('../essentials.php');

// Verify that the interfaces have been set.
$interfaces = \Base\User::getUserInterfaces();

if(!$interfaces) {
    header("Location: /setup/");
    die();
}

// Verify that user credentials have been set
if(!\Base\User::getCredentials()) {
    header("Location: /login/");
    die();
}

// Fetch session
$session = \Base\User::getSession();


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

// Checks whether the user is connected to OVPN or not
$connected = \Network\Adapter::isConnectedToOVPN();

$data = array(
    'page' => 'index'
);
require('./assets/template/top.php');
?>
<!-- Visas när personen inte är ansluten till OVPN -->
<div class="choose-server <?php if($connected) { echo 'hidden'; } ?>">
    <form role="form" method="post" action="#" id="connect">
        <div class="form-group">
            <select class="form-control" id="serverIp">
                <option value="0">Välj bäst server automatiskt</option>
            </select>
        </div>
        <!-- Denna select meny visas och är populerad med tilläggstjänsterna som är aktiva. Om inga tilläggstjänster finns är den dold. -->
        <div class="choose-addon form-group <?php if(!$addons) { echo 'hidden'; } ?>">
            <select class="form-control" id="addonId">
                <option value="normal" selected>Ingen tilläggstjänst</option>
                <?php
                if($addons) {
                    foreach($addons as $addonType => $addonData) {

                        if($addonType == 'proxy') {
                            continue;
                        }

                        echo '<option value="' . $addonType . '">' . \Base\String::addonAbbreviationToText($addonType) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        <div class="checkbox text-center">
            <label>
                <input type="checkbox" id="killswitch" checked> Aktivera kill-switch
            </label>
        </div>
        <div class="form-group text-center">
            <button type="submit" name="submit" class="btn btn-primary">Anslut</button>
        </div>
    </form>
</div>

<!-- Visas när personen är ansluten till OVPN-->
<div class="disconnect <?php if(!$connected) { echo 'hidden'; } ?>">
    <form role="form" method="post" action="#" id="disconnect">
        <div class="form-group text-center">
            <button type="submit" name="submit" class="btn btn-primary">Koppla ner OVPN</button>
        </div>
    </form>
</div>

<!-- Fylls på med information när personen har blivit ansluten till OVPN -->
<div class="connection-details <?php if(!$connected) { echo 'hidden'; } ?>">
    <h4 class="text-center">Anslutningsdetaljer</h4>
    <table class="table table-striped">
        <tr>
            <td><strong>Ansluten</strong></td>
            <td>Ja</td>
        </tr>
        <tr>
            <td><strong>IPv4</strong></td>
            <td><span id="internal_ip"><?php echo \Network\IP::internal(); ?></span> / <span id="external_ip"></span></td>
        </tr>
        <tr>
            <td><strong>PTR</strong></td>
            <td id="ip4_ptr"></td>
        </tr>
        <tr>
            <td><strong>IPv6</strong></td>
            <td>-</td>
        </tr>
        <tr>
            <td><strong>Server</strong></td>
            <td><span id="server_name"></span>, <span id="server_country"></td>
        </tr>
    </table>
</div>
<!-- Traffic graph -->
<div class="traffic <?php if(!$connected) { echo 'hidden'; } ?>">
    <h4 class="text-center">Trafik</h4>
    <div class="text-center">
        <span style="color:#6D88AD"><i class="fa fa-arrow-up"></i>&nbsp;<span id="output">-</span> Mbit/s</span>
        <span style="color:#1EB300;margin-left: 10px;"><i class="fa fa-arrow-down"></i>&nbsp;<span id="input">-</span> Mbit/s</span>
    </div>
    <div class="graph hidden-xs" style="min-width: 400px; height: 300px; margin: 0 auto"></div>
</div>
<div id="dataholder" data-center="<?php echo \Base\User::getBestDatacenter(); ?>" data-connected="<?php echo $connected;  ?>" data-server_ip="<?php echo \Network\IP::server(); ?>"></div>
<?php require('./assets/template/footer.php'); ?>