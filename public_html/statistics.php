<?php
require('../essentials.php');

// Verify that user credentials have been set
if(!\Base\User::getCredentials()) {
    header("Location: /login/");
    die();
}

$data = array(
    'page' => 'stats'
);
require('./assets/template/top.php');
?>

<!-- Traffic graph -->
<h4 class="text-center">Senaste 60 minuter</h4>
    <div class="text-center">
        <span style="color:#6D88AD"><i class="fa fa-arrow-up"></i>&nbsp;<span id="minutes_output">-</span> MB</span>
        <span style="color:#1EB300;margin-left: 10px;"><i class="fa fa-arrow-down"></i>&nbsp;<span id="minutes_input">-</span> MB</span>
    </div>
<div class="minutes_graph hidden-xs" style="min-width: 400px; height: 300px; margin: 0 auto"></div>

<h4 class="text-center">Senaste 24 timmar</h4>
<div class="text-center">
    <span style="color:#6D88AD"><i class="fa fa-arrow-up"></i>&nbsp;<span id="hours_output">-</span> MB</span>
    <span style="color:#1EB300;margin-left: 10px;"><i class="fa fa-arrow-down"></i>&nbsp;<span id="hours_input">-</span> MB</span>
</div>
<div class="hours_graph hidden-xs" style="min-width: 400px; height: 300px; margin: 0 auto"></div>

<h4 class="text-center">Senaste 31 dagar</h4>
<div class="text-center">
    <span style="color:#6D88AD"><i class="fa fa-arrow-up"></i>&nbsp;<span id="days_output">-</span> MB</span>
    <span style="color:#1EB300;margin-left: 10px;"><i class="fa fa-arrow-down"></i>&nbsp;<span id="days_input">-</span> MB</span>
</div>
<div class="days_graph hidden-xs" style="min-width: 400px; height: 300px; margin: 0 auto"></div>



<?php require('./assets/template/footer.php'); ?>