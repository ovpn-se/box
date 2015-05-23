function verifyCredentials(username,password)
{

    var status = false;
    var error  = null;

    $.ajax({
        type: "POST",
        url:  "/api/authenticate",
        data: {
            username: username,
            password: password
        },
        async: false,
        cache: false,
        timeout:60000,
        success: function () {

            status = true;

        },
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

             error = err.error;

        }
    });

    if(status) {
        return {
            auth: true
        };
    } else {
        return {
            auth: false,
            error: error
        };
    }

}

function hideMessage()
{
    $(".info").addClass('hidden');
    $(".error").addClass('hidden');
}

function displayMessage(type, title, message) {

    var display = '',
        element,
        hide,
        icon;

    if(type == 'info') {


        element = $(".info");
        hide    = $(".error");
        icon    = 'cog fa-spin';
    } else {

        hide     = $(".info");
        element  = $(".error");
        icon    = 'exclamation';
    }

    if(title) {
        display += '<h4><i class="fa fa-' + icon + '"></i> ' + title + '</h4>';
    }

    display += '<p>' + message + '</p>';

    hide.addClass('hidden');
    element.html(display).removeClass('hidden');
}

function logout()
{

    $.ajax({
        type: "DELETE",
        url:  "/api/authenticate",
        async: false,
        cache: false,
        timeout: 60000,
        error: function(xhr, textStatus, errorThrown ) {

            try {
                var err = JSON.parse(xhr.responseText);
            } catch(error) {
                var err = [{"error": "Ett tekniskt fel har skett."}];
            }

            alert(err.error);

        }
    });

    window.location = '/login/';
}

function dynamicSort(property) {
    var sortOrder = 1;
    if(property[0] === "-") {
        sortOrder = -1;
        property = property.substr(1);
    }
    return function (a,b) {
        var result = (a[property] < b[property]) ? -1 : (a[property] > b[property]) ? 1 : 0;
        return result * sortOrder;
    }
}