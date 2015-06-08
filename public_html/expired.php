<?php
require('../essentials.php');

// If credentials have been set redirect to main menu
if(\Base\User::getCredentials()) {
    header("Location: /");
    die();
}

$data = array(
    'page' => 'expired',
    'title' => 'Förnya abonnemanget'
);

require('./assets/template/top.php');

?>
<div class="alert alert-danger" role="alert">Tiden på ditt abonnemang har tagit slut.</div>
Du har ingen tid kvar på ditt abonnemang. För att kunna ansluta måste du <a href="https://www.ovpn.se/account/login/">förnya abonnemanget</a>. <br /><br />

Logga ut och logga in igen när du har betalat för att kunna fortsätta använda OVPNbox.<br /><br />
<?php require('./assets/template/footer.php'); ?>