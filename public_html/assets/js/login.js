$(function () {



    /**
     * Används när man ska ansluta. Antingen har personen valt 'Välj bäst server' eller så
     * har personen valt en specifik server.
     */
    $("#login").submit(function(event) {
        event.preventDefault();

        // Hide error message
        var error = $(".error");
        error.addClass('hidden');

        // Update button
        var button = $("#login").find('button');
        button.html('<i class="fa fa-circle-o-notch fa-spin"></i> Verifierar');

        // Sent login attempt
        var login =  verifyCredentials($("#username").val(), $("#password").val());

        // Verify
        if(login.auth) {

            // Success. Redirect to main page
            window.location = '/';
        } else {

            // Show error message
            error.html(login.error).removeClass('hidden');
            button.html('Logga in');
        }
    });

});
