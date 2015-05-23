<?php
require('../essentials.php');

// If credentials have been set redirect to main menu
if(\Base\User::getCredentials()) {
    header("Location: /");
    die();
}

$data = array(
    'page' => 'login'
);

require('./assets/template/top.php');

?>
<div class="col-sm-6">
    <form role="form" method="post" id="login">
        <div class="form-group">
            <label for="username">Användarnamn</label>
            <input type="text" class="form-control" id="username" name="username" value="" />
        </div>
        <div class="form-group">
            <label for="password">Lösenord</label>
            <input type="password" class="form-control" id="password" name="password" />
        </div>
        <div class="form-group text-center">
            <br />
            <button type="submit" name="submit" class="btn btn-default">Logga in</button>
        </div>
    </form>
</div>
<div class="col-sm-6">
    <p class="text">
        För att börja använda OVPNbox så måste du logga in på ditt konto på OVPN. Ange ditt användarnamn och lösenord.
    </p>
</div>

<?php require('./assets/template/footer.php'); ?>