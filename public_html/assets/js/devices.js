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

                el.child().removeClass('fa-shield').addClass('fa-pause');
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

                el.child().removeClass('fa-pause').addClass('fa-shield');
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

});
