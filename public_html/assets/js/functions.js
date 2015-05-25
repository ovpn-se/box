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