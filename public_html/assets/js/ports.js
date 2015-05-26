$(function () {

    /**
     * To open a port
     */
    $("#port_form").submit(function(event) {
        event.preventDefault();

        // Hide error message
        var error = $(".error"), device = $("#device"), port_number = $("#port_number"), type = $("#port_protocol"),  button = $("#port_form").find('button'), table = $('.table'), info = $(".info");
        error.addClass('hidden');

        displayMessage('info', 'Öppnar port', 'OVPNbox arbetar på att vidarebefordra porten till enheten.');

        // Update button
        button.html('<i class="fa fa-circle-o-notch fa-spin"></i> Verifierar');
        button.prop("disabled",true);

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
            success: function () {

                $('.tbody').append(
                        '<tr>' +
                            '<td>' + device.find("option:selected").text() + '</td>' +
                            '<td>' + port_number.val() + '</td>' +
                            '<td>' + type.find("option:selected").text() + '</td>' +
                            '<td><a href="javascript:void(0);" title="Porten har vidarebefordrats!"><i class="fa fa-check"></i></a></td></tr>');

                if(table.hasClass('hidden')) {
                    table.removeClass('hidden');
                    $(".ports-display").removeClass('hidden');
                }

                info.addClass('hidden');
                button.prop("disabled",false);

            },
            error: function(xhr, textStatus, errorThrown ) {
                try {
                    var err = JSON.parse(xhr.responseText);
                } catch(error) {
                    var err = [{"error": "Ett tekniskt fel har skett."}];
                }

                displayMessage('error', 'Fel', err.error);
                button.html('Öppna port');
                button.prop("disabled",false);
            }
        });


    });

    // Form that gets executed when user tries to delete a port forward
    $("body").on('click', '.delete_port', function() {

        // Hide error message
        var error = $(".error"), device = $("#device"), port_number = $("#port_number"), type = $("#port_protocol"),  button = $("#port_form").find('button'), table = $('.table');
        error.addClass('hidden');

        displayMessage('info', 'Tar bort port', 'OVPNbox arbetar på att stänga porten till enheten.');

        $.ajax({
            type: "DELETE",
            url:  "/api/port",
            data: {
                ip: device.val(),
                port: port_number.val(),
                type: type.val()
            },
            async: true,
            cache: false,
            timeout:120000,
            success: function () {

                $("#port-" + $(this).data('portid')).remove();
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
