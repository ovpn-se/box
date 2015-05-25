$(function () {

    $("#username").focus();

    /**
     * Används när man ska ansluta. Antingen har personen valt 'Välj bäst server' eller så
     * har personen valt en specifik server.
     */
    $("#login").submit(function(event) {
        event.preventDefault();

        // Hide error message
        var error = $(".error"), username = $("#username"), password = $("#password"), button = $("#login").find('button');
        error.addClass('hidden');

        displayMessage('info', 'Inloggning pågår', 'OVPNbox arbetar på att verifiera inloggningsuppgifterna & ändra inställningar i boxen.');

        // Update button
        button.html('<i class="fa fa-circle-o-notch fa-spin"></i> Verifierar');

        $.ajax({
            type: "POST",
            url:  "/api/authenticate",
            data: {
                username: username,
                password: password
            },
            async: true,
            cache: false,
            timeout:120000,
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
                button.html('Logga in');
            }
        });
    });

});
