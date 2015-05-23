$(function () {
    loadGraph('minutes');
    loadGraph('hours');
    loadGraph('days');

});

function loadGraph(interval)
{
    var input_display = $("#"+interval+"_input"),
        output_display = $("#"+interval+"_output");

    $.ajax({
        type: "GET",
        url:  "/api/traffic/history/" + interval,
        async: true,
        cache: false,
        timeout:5000,
        success: function (retval) {

            Highcharts.setOptions({
                global : {
                    timezoneOffset: -120
                }
            });

            // User is not accessing through mobile. Create the chart
            $('.'+interval+'_graph').highcharts('StockChart', {
                rangeSelector: {
                    enabled: false
                },
                navigator: {
                    enabled: false
                },
                scrollbar: {
                    enabled: false
                },
                yAxis: {
                    min: 0
                },
                series : [{
                    name : 'Uppladdat (MB)',
                    color: '#6D88AD',
                    data : (function () {

                        // generate an array of random data
                        var data = [], time = new Date(), x = new Date(), size = Object.size(retval.traffic);

                        for (i = 0; i < size; i++) {

                            data.push([
                                (new Date(retval.traffic[i].date)).getTime(),
                                retval.traffic[i].output
                            ]);
                        }
                        return data;
                    }())
                },{
                    name : 'Nedladdat (MB)',
                    color: '#1EB300',
                    data : (function () {

                        // generate an array of random data
                        var data = [], time = new Date(), x = new Date(), size = Object.size(retval.traffic);

                        for (i = 0; i < size; i++) {

                            data.push([
                                (new Date(retval.traffic[i].date)).getTime(),
                                retval.traffic[i].input
                            ]);
                        }
                        return data;
                    }())
                }
                ]
            });





            // Display numeric values
            input_display.text(retval.summary.input.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '));
            output_display.text(retval.summary.output.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' '));

        }
    });


}

Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};