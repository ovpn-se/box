$(function () {

    /**
     * To open a port
     */
    $("#port_form").submit(function(event) {
        event.preventDefault();

        // Hide error message
        var error = $(".error"), device = $("#device"), port_number = $("#port_number"), type = $("#port_protocol"),  button = $("#port_form").find('button');
        error.addClass('hidden');

        displayMessage('info', 'Öppnar port', 'OVPNbox arbetar på att vidarebefordra porten till enheten.');

        // Update button
        button.html('<i class="fa fa-circle-o-notch fa-spin"></i> Verifierar');


        $.ajax({
            type: "POST",
            url:  "/api/port",
            data: {
                ip: device.val(),
                port: port_number.val(),
                type: type.val()
            },
            async: true,
            cache: false,
            timeout:120000,
            success: function (output) {

                //window.location = '/';
                console.log(output);

            },
            error: function(xhr, textStatus, errorThrown ) {
                try {
                    var err = JSON.parse(xhr.responseText);
                } catch(error) {
                    var err = [{"error": "Ett tekniskt fel har skett."}];
                }

                displayMessage('error', 'Fel', err.error);
                button.html('Öppna port');
            }
        });
    });

});
