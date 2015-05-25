$(function () {

    $("#username").focus();

    /**
     * Används när man ska ansluta. Antingen har personen valt 'Välj bäst server' eller så
     * har personen valt en specifik server.
     */
    $("#login").submit(function(event) {
        event.preventDefault();

        console.log('login1');

        // Hide error message
        var error = $(".error"), username = $("#username"), password = $("#password"), button = $("#login").find('button');
        error.addClass('hidden');

        //displayMessage('info', 'Inloggning pågår', 'OVPNbox arbetar på att verifiera inloggningsuppgifterna & ändra inställningar i boxen.');
        console.log('login2');
        // Update button
        button.html('<i class="fa fa-circle-o-notch fa-spin"></i> Verifierar');
        console.log('login3');

        $.ajax({
            type: "POST",
            url:  "/api/authenticate",
            data: {
                username: username.val(),
                password: password.val()
            },
            async: true,
            cache: false,
            timeout:120000,
            success: function () {
                console.log('login4');
                window.location = '/';

            },
            error: function(xhr, textStatus, errorThrown ) {
                console.log('login');
                try {
                    var err = JSON.parse(xhr.responseText);
                } catch(error) {
                    var err = [{"error": "Ett tekniskt fel har skett."}];
                }

                displayMessage('error', 'Fel', err.error);
                button.html('Logga in');
            }
        });
        console.log('login5');
    });

});
