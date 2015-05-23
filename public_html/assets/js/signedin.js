$(function () {

    // Körs när driftinformationen hämtas.
    setTimeout(function(){

        getServers();

    }, 2000);


    /**
     * Används när man ska ansluta. Antingen har personen valt 'Välj bäst server' eller så
     * har personen valt en specifik server.
     */
    $("#connect").submit(function(event) {
        event.preventDefault();

        // Hämta värdet i selectmenyn
        var ip = $("#serverIp").val();
        console.log(ip);

        // Verifiera resultatet
        if(ip != 0) {

            // En specifik server har valts.
            console.log('Anslut till: ' + ip);

        } else {

            console.log('automatisk');
            // Vi ska välja den bästa servern för personen.
            getBestServer();

        }

        return false;
    });

    // Formulär när man klickar på disconnect
    $("#disconnect").submit(function(event) {
        event.preventDefault();

        // Visa select meny så att man kan ansluta till servrar
        $(".choose-server").removeClass('hidden');

        // Göm anslutningsdetaljer & koppla ner knapp
        $(".disconnect").addClass('hidden');
        $(".connection-details").addClass('hidden');
        return true;
    });




});

// Bör inte köras ofta. Max en gång per dag. Det är tänkt att denna funktion hämtar
// datahallar vi befinner oss i och returnerar IP-adresser. Därefter så bör du bygga en funktion som gör traceroutes
// till IP-adresserna för att kolla vilken datahall som personen har bäst anslutning mot. Detta värde borde sparas
// i minst 24 timmar eftersom det är inte sannolikt att tracerouten byts ofta.
function getBestServer()
{

    // Vi börjar med att hämta datahallar
    //var datacenters = getDatacenters();

    // När vi har hittat datahallen med bäst anslutning så hämtar vi belastning på den
    // Något i stil med:
    //getLoad(datacenters[0].slug)

    // Därefter sorterar vi resultateten baserat på lägst belastning (procentuell) och ansluter till den IP-adressen.

}


function getServers()
{
    var errorDisplay = $(".error");
    var loadDisplay  = $(".load");

    $.ajax({
        type: "GET",
        url:  "http://localhost/v1/api/client/servers",
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
    return true;
}

function getDatacenters()
{

    console.log('Kör: getDatacenters()');

    var errorDisplay = $(".error");
    var loadDisplay  = $(".load");

    $.ajax({
        type: "GET",
        url:  "http://localhost/v1/api/client/datacenters",
        async: true,
        cache: false,
        beforeSend: function(xhr) {
            loadDisplay.removeClass('hidden');
            errorDisplay.addClass('hidden');
            return true;
        },
        timeout:20000,
        success: function (output) {

            console.log(output);

            for (i = 0; i < output.length; i++) {

                console.log(output[i]);

                /**
                 * Detta bör du göra traceroutes mot och spara resultat på fil eller cache.
                 * @type {*}
                 */
                var ip = output[i].ip;
                var slug = output[i].slug;

                // När traceroutes är gjort kan du köra getLoad(slug) för att få fram belastning
                getLoad(slug);
            }

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
    return true;
}

/**
 * Hämtar belastning på servrar i en specifik datahall
 * @returns {boolean}
 */
function getLoad(datacenter) {

    var errorDisplay = $(".error");
    var loadDisplay  = $(".load");

    $.ajax({
        type: "GET",
        url:  "http://localhost/v1/api/client/servers/"+datacenter,
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
                    '<option value="' + data.ip + '">' + data.name + ' | ' + data.currentLoad.bandwidth + '% belastning</option>';
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
    return true;
}