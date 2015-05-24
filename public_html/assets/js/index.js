$(function () {

    // Fetch data regarding the current state
    var holder = $("#dataholder");

    // Check if user is connected to OVPN
    if(holder.data('connected')) {

        // Fetch the external IP
        getExternalIP();

        // Fetch server & PTR
        getServerData();

        var input_display = $("#input"),
            output_display = $("#output");

        // Check if the user access the gui using the mobile
        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {

            setInterval(function () {
                $.ajax({
                    type: "GET",
                    url:  "/api/traffic",
                    async: true,
                    cache: false,
                    timeout:5000,
                    success: function (retval) {
                        // Display numeric values
                        input_display.text(retval.traffic.input);
                        output_display.text(retval.traffic.output);
                    }
                });
            }, 3000);
        } else {

            Highcharts.setOptions({
                global : {
                    timezoneOffset: -120
                }
            });

            // User is not accessing through mobile. Create the chart
            $('.graph').highcharts('StockChart', {
                chart : {
                    events : {
                        load : function () {

                            // set up the updating of the chart each second
                            var input = this.series[1],
                                output = this.series[0];

                            setInterval(function () {
                                $.ajax({
                                    type: "GET",
                                    url:  "/api/traffic",
                                    async: true,
                                    cache: false,
                                    timeout:5000,
                                    success: function (retval) {

                                        var x = (new Date()).getTime();

                                        // Plot chart
                                        input.addPoint([x, retval.traffic.input], true, true);
                                        output.addPoint([x, retval.traffic.output], true, true);

                                        // Display numeric values
                                        input_display.text(retval.traffic.input);
                                        output_display.text(retval.traffic.output);

                                    }
                                });
                            }, 3000);
                        }
                    }
                },
                rangeSelector: {
                    enabled: false
                },
                navigator: {
                    enabled: false
                },
                scrollbar: {
                    enabled: false
                },
                yAxis: {
                    min: 0
                },
                series : [{
                    name : 'Uppladdningshastighet',
                    color: '#6D88AD',
                    data : (function () {
                        // generate an array of random data
                        var data = [], time = (new Date()).getTime(), i;

                        for (i = -100; i <= 0; i += 1) {
                            data.push([
                                time + i * 3000,
                                null
                            ]);
                        }
                        return data;
                    }())
                },{
                    name : 'Nedladdningshastighet',
                    color: '#1EB300',
                    data : (function () {
                        // generate an array of random data
                        var data = [], time = (new Date()).getTime(), i;

                        for (i = -100; i <= 0; i += 1) {
                            data.push([
                                time + i * 3000,
                                null
                            ]);
                        }
                        return data;
                    }())
                }
                ]
            });
        }


        // Start fetching traffic data


    } else {

        // Display message
        displayMessage('info', 'Driftinformation hämtas...', 'Driftinformation och tester görs för att hitta vilken server du har bäst anslutning till.');

        // User is not connected. Let's fetch the servers.
        getServers();
    }

    /**
     * Används när man ska ansluta. Antingen har personen valt 'Välj bäst server' eller så
     * har personen valt en specifik server.
     */
    $("#connect").submit(function(event) {
        event.preventDefault();

        // Hämta värdet i selectmenyn
        var ip = $("#serverIp").val();
        //console.log('valid ip: ' +ip);

        // Verifiera resultatet
        if(ip != 0) {

            // En specifik server har valts.
            //console.log('Anslut till: ' + ip);

        } else {

            //console.log('automatisk');
            // Vi ska välja den bästa servern för personen.
            var serverData = getBestServer();
            //console.log(serverData);
            ip  = serverData.server.ip;

        }

        //console.log('ip: ' + ip);
        connect(ip);

        return false;
    });

    // Formulär när man klickar på disconnect
    $("#disconnect").submit(function(event) {
        event.preventDefault();

        disconnect();

        // Visa select meny så att man kan ansluta till servrar
        $(".choose-server").removeClass('hidden');

        // Göm anslutningsdetaljer & koppla ner knapp
        $(".disconnect").addClass('hidden');
        $(".connection-details").addClass('hidden');
        return true;
    });

    // Form that gets executed when user tries to update the box
    $("body").on('click', '#update_box', function() {

        $(".update").addClass('hidden');

        displayMessage('info', 'Uppdatering pågår', 'OVPNbox håller på att uppdateras.');
        updateBox();
        return true;
    });


    setTimeout(
        function(){

            // Check for available updates.
            checkAvailableUpdate();
        }, 3000);

});

function updateBox()
{
    $.ajax({
        type: "POST",
        url:  "/api/update",
        async: true,
        timeout:120000,
        success: function (output) {

            displayMessage('info', 'Uppdatering lyckades!', 'OVPNbox har uppdateras. Gränssnittet kommer snart att laddas om.');
            setTimeout(
                function(){
                    window.location = '/';
                },
                4500
            );

        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            console.log(err);
            return true;

        }
    });
    return true;
}

function checkAvailableUpdate()
{
    $.ajax({
        type: "GET",
        url:  "/api/update",
        async: true,
        timeout:60000,
        success: function (output) {

            var element = $(".update");
            element.html('<h4>En uppdatering finns tillgänglig</h4>' +
                'En <a href="https://github.com/ovpn-se/box/commit/' + output.commit.full + '" target="_blank" title="Visa uppdateringen">ny uppdatering <i>(' + output.commit.short + ')</i></a> av OVPNbox finns tillgänglig!<br /><br />' +
                '<form role="form" action="#">' +
                    '<div class="form-group">' +
                        '<button type="button" name="submit" id="update_box" class="btn btn-success">Uppdatera OVPNbox</button>' +
                    '</div>' +
                '</form>');
            element.removeClass('hidden');
        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            console.log(err);
            return true;

        }
    });
    return true;
}

function getServerData()
{

    $.ajax({
        type: "GET",
        url:  "https://www.ovpn.se/v1/api/client/server/" + $("#dataholder").data('server_ip'),
        async: true,
        timeout:20000,
        success: function (output) {

            $("#server_name").text(output.name);
            $("#server_country").text(output.country);
            return true;

        }
    });
}

// Bör inte köras ofta. Max en gång per dag. Det är tänkt att denna funktion hämtar
// datahallar vi befinner oss i och returnerar IP-adresser. Därefter så bör du bygga en funktion som gör traceroutes
// till IP-adresserna för att kolla vilken datahall som personen har bäst anslutning mot. Detta värde borde sparas
// i minst 24 timmar eftersom det är inte sannolikt att tracerouten byts ofta.
function getBestServer()
{

    // Fetch data regarding the current state
    var holder = $("#dataholder");
    var datacenter = holder.data('center');

    // Check if we already know which datacenter is the best for the user
    if(holder.data('center') == false) {

        // We don't know which datacenter is best yet.
        var getDatacenter = getBestDatacenter();
        datacenter = getDatacenter.datacenter;
    }

    return getServer(datacenter);
}


function connect(ip) {

    displayMessage('info', 'Ansluter...', 'OVPNbox försöker ansluta till servern.');

    var errorDisplay = $(".error");
    var loadDisplay  = $(".load");

    $.ajax({
        type: "POST",
        url:  "/api/connect",
        data: {
            ip: ip,
            addon:      $("#addonId").val(),
            killswitch: $("#killswitch").is(":checked")
        },
        async: true,
        timeout:60000,
        success: function () {

            displayMessage('info', 'Lyckades!', 'OVPNbox har anslutit. Vänta medans de sista inställningarna görs.');

            // Set interval to check every 1,5 seconds if connection is complete
            setInterval(function () {

                $.ajax({
                    type: "GET",
                    url:  "/api/connected",
                    async: true,
                    timeout:60000,
                    success: function () {
                        window.location = '/';
                    }
                });
            }, 1500);
        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            displayMessage('error', 'Fel', err.error);
            return true;

        }
    });
    return true;
}

function disconnect() {

    displayMessage('info', 'Kopplar ner', 'OVPNbox håller på att koppla ner från servern.');

    var errorDisplay = $(".error");
    var loadDisplay  = $(".load");

    $.ajax({
        type: "POST",
        url:  "/api/disconnect",
        async: true,
        timeout:60000,
        success: function () {

            window.location = '/';

        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }


            displayMessage('error', 'Fel', err.error);
            return true;

        }
    });
    return true;
}

function getExternalIP()
{

    $.ajax({
        type: "GET",
        url:  "/api/ip",
        async: true,
        success: function (output) {

            $("#external_ip").text(output.ip);
            getPTRforIP(output.ip)

        }
    });
    return true;
}

function getPTRforIP(ip)
{

    $.ajax({
        type: "GET",
        url:  "https://www.ovpn.se/v1/api/client/ptr/" + ip,
        async: true,
        success: function (output) {

            $("#ip4_ptr").text(output.ptr);

        }
    });
    return true;
}


function getServers()
{

    displayMessage('info', 'Hämtar servrar', 'OVPNbox hämtar information om tilgängliga servrar.');

    var errorDisplay = $(".error");
    var loadDisplay  = $(".load");

    $.ajax({
        type: "GET",
        url:  "https://www.ovpn.se/v1/api/client/servers",
        async: true,
        cache: false,
        beforeSend: function(xhr) {
            loadDisplay.removeClass('hidden');
            errorDisplay.addClass('hidden');
            return true;
        },
        timeout:20000,
        success: function (output) {

            var serverSelect = '';

            for (var i = 0; i < output.length; i++) {
                var data = output[i];

                serverSelect +=
                    '<option value="' + data.ip + '">' + data.name + '</option>';
            }

            console.log(serverSelect);

            $('#serverIp').append(serverSelect);
            $(".choose-server").removeClass('hidden');
            loadDisplay.addClass('hidden');
            return true;

        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            loadDisplay.addClass('hidden');
            errorDisplay.html(err.error).removeClass('hidden');
            return true;

        }
    });
    hideMessage();
    return true;
}

function getBestDatacenter()
{

    displayMessage('info', 'Nätverkstester', 'OVPNbox undersöker vilken datahall du har bäst anslutning till.');

    return JSON.parse($.ajax({
        type: "GET",
        url:  "/api/datacenter",
        async: false,
        cache: false,
        timeout:60000,
        success: function (output) {

            $("#dataholder").data('center', output.datacenter);
            return output.datacenter;

        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            return false;

        }
    }).responseText);
}

/**
 * Hämtar belastning på servrar i en specifik datahall
 * @returns {boolean}
 */
function getServer(datacenter) {

    displayMessage('info', 'Undersöker belastning', 'OVPNbox kollar nu vilken VPN-server som har lägst belastning.');

    return JSON.parse($.ajax({
        type: "GET",
        url:  "/api/datacenter/"+datacenter,
        async: false,
        cache: false,
        timeout:60000,
        success: function (output) {

            $("#dataholder").data('server', output.server);
            return output.server;
        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            return false;

        }
    }).responseText);
}