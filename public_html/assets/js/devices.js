$(function () {

    var body = $("body");

    // Executed when user tries to remove a bypass
    body.on('click', '.remove_bypass', function() {

        // Hide error message
        var error = $(".error"), info = $(".info"), el = $(this);
        error.addClass('hidden');

        displayMessage('info', 'Aktiverar skydd', 'OVPNbox arbetar på att skydda enheten bakom OVPN.');

        $.ajax({
            type: "DELETE",
            url:  "/api/bypass",
            data: {
                ip: el.data('ip')
            },
            async: true,
            cache: false,
            timeout:120000,
            success: function () {

                el.children().removeClass('fa-shield').addClass('fa-pause');
                el.attr('title', 'Klicka för att pausa enhetens skydd').removeClass('remove_bypass').addClass('activate_bypass');

                info.addClass('hidden');
            },
            error: function(xhr, textStatus, errorThrown ) {
                try {
                    var err = JSON.parse(xhr.responseText);
                } catch(error) {
                    var err = [{"error": "Ett tekniskt fel har skett."}];
                }

                displayMessage('error', 'Fel', err.error);
            }
        });
    });

    // Executed when user tries to add a bypass
    body.on('click', '.activate_bypass', function() {

        // Hide error message
        var error = $(".error"), info = $(".info"), el = $(this);
        error.addClass('hidden');

        displayMessage('info', 'Inaktiverar skydd', 'OVPNbox arbetar på att skydda enheten bakom OVPN.');

        $.ajax({
            type: "POST",
            url:  "/api/bypass",
            data: {
                ip: el.data('ip')
            },
            async: true,
            cache: false,
            timeout:120000,
            success: function () {

                el.children().removeClass('fa-pause').addClass('fa-shield');
                el.attr('title', 'Klicka för att skydda enheten bakom OVPN').removeClass('activate_bypass').addClass('remove_bypass');

                info.addClass('hidden');
            },
            error: function(xhr, textStatus, errorThrown ) {
                try {
                    var err = JSON.parse(xhr.responseText);
                } catch(error) {
                    var err = [{"error": "Ett tekniskt fel har skett."}];
                }

                displayMessage('error', 'Fel', err.error);
            }
        });
    });

    // Executed when user wants to add a static mapping
    body.on('click', '.edit_device', function() {

        // Hide error message
        var error = $(".error"), info = $(".info"), el = $(this), parent = el.data('rowid'), staticip = $(".staticips");
        error.addClass('hidden');

        displayMessage('info', 'Ändrar IP', 'OVPNbox arbetar på att allokera en statisk IP-adress till enheten.');

        $.ajax({
            type: "POST",
            url:  "/api/static",
            data: {
                hostname: el.data('hostname'),
                mac: el.data('mac')
            },
            async: true,
            cache: false,
            timeout:120000,
            success: function (output) {

                $("#" + el.data('rowid')).remove();

                staticip.removeClass('hidden');
                staticip.find('tbody').append(
                    '<tr>' +
                        '<td>' + device.find("option:selected").text() + '</td>' +
                        '<td>' + port_number.val() + '</td>' +
                        '<td>' + type.find("option:selected").text() + '</td>' +
                        '<td><a href="javascript:void(0);" title="Porten har vidarebefordrats!"><i class="fa fa-check"></i></a></td></tr>');

                info.addClass('hidden');
            },
            error: function(xhr, textStatus, errorThrown ) {
                try {
                    var err = JSON.parse(xhr.responseText);
                } catch(error) {
                    var err = [{"error": "Ett tekniskt fel har skett."}];
                }

                displayMessage('error', 'Fel', err.error);
            }
        });

    });

});
