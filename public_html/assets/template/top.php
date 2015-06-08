<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title><?php if(isset($data['title'])) { echo $data['title'] . ' | OVPNbox'; } ?> </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/x-icon" href="/favicon.ico" />

    <!-- stylesheets -->
    <link rel="stylesheet" type="text/css" href="/assets/css/theme.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/main.css">

    <!-- javascript -->
    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script src="/assets/js/functions.js"></script>

    <?php
        if(isset($data['page'])) {
            if(file_exists(DOCUMENT_ROOT . '/public_html/assets/js/' . $data['page'] . '.js')) {
                echo '<script src="/assets/js/' . $data['page'] . '.js?v=' . md5(DOCUMENT_ROOT . '/public_html/assets/js/' . $data['page'] . '.js') . '"></script>';
            }

            if($data['page'] == 'index' || $data['page'] == 'stats') {
                echo '<script src="/assets/js/highstock.js"></script>';
            }
        }
    ?>
</head>

<body id="signup">
<div class="container">
    <div class="row header">
        <div class="col-md-12">
            <h3 class="logo">
                <a href="/" title="OVPN.se"><img src="/assets/img/ovpn-logo.png" alt="OVPN.se" title="OVPN.se"></a>
            </h3>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="wrapper clearfix">
                <div class="formy">
                    <div class="row">
                        <?php if(!in_array($data['page'], array('setup', 'login'))) { ?>
                        <nav class="navbar navbar-default">
                            <div class="container-fluid">
                                <!-- Brand and toggle get grouped for better mobile display -->
                                <div class="navbar-header">
                                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-navbar-collapse-1">
                                        <span class="sr-only">Toggle navigation</span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                        <span class="icon-bar"></span>
                                    </button>
                                </div>

                                <!-- Collect the nav links, forms, and other content for toggling -->
                                <div class="collapse navbar-collapse text-center" id="bs-navbar-collapse-1">
                                    <ul class="nav navbar-nav">
                                        <li <?php if($data['page'] == 'index')   { echo 'class="active"'; } ?>><a href="/">Anslutning</a></li>
                                        <li <?php if($data['page'] == 'stats')   { echo 'class="active"'; } ?>><a href="/stats/">Statistik</a></li>
                                        <li <?php if($data['page'] == 'devices') { echo 'class="active"'; } ?>><a href="/devices/">Enheter</a></li>
                                        <li <?php if($data['page'] == 'ports')   { echo 'class="active"'; } ?>><a href="/ports/">Portar</a></li>
                                        <li <?php if($data['page'] == 'system')  { echo 'class="active"'; } ?>><a href="/system/">System</a></li>
                                    </ul>
                                </div><!-- /.navbar-collapse -->
                            </div><!-- /.container-fluid -->
                        </nav>
                        <?php } ?>
                        <div class="col-sm-12">
                            <div class="error alert alert-danger hidden"></div>
                            <div class="info alert alert-info hidden"></div>
                            <div class="update alert alert-info hidden"></div>