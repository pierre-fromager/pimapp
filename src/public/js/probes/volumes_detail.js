var detailChart = null;

function getDetailTransfo(data) {
    var labels = [];
    series = [];
    for (nbseries = 0; nbseries < data.length; nbseries++) {
        if (nbseries === 0) {
            labels = data[nbseries].map(function (i) {
                return i['dt'];
            });
        }
        var snList = data[nbseries].map(function(i) {return i['sn']});
        series.push(
            getChartPoint(
                data[nbseries].map(
                    function (i) {
                        return  parseInt(i['_avg']) / 1000
                    }
                ),
                'avgDaylyProbe', snList[0]
            )
        );
    }
    return  {series: series, labels: labels};
}

function getChartPoint(value, classN, meta) {
    return {
        value: value,
        className: classN,
        meta: meta
    }
}

function getCriterias() {
    $.when($.ajax({
        url: ROOT_URL + 'probes/criteriasjson',
        method: 'GET',
    })).then(function (criterias, textStatus, jqXHR) {
        criterias = {
            dt : $('#detailVolume').attr('data-dt'),
            boxsn : $('#detailVolume').attr('data-boxsn'),
            sn : $('#detailVolume').attr('data-sn'),
        }
        getDetailDatas(criterias);
    });
}

function getDetailDatas(criterias, urlAction) {
    $.ajax({
        url: ROOT_URL + "probes/voldetailjson",
        method: 'POST',
        data: {pattern: criterias},
        dataType: "json",
        success: function (data) {
            detailChart = drawLineChart(
                '#detailVolume', 
                getDetailTransfo(data),
                30
            );
        },
        error: function (data) {
            console.log('Error getProbeDatas', data);
        }
    });
}

function drawLineChart(targetElementId, data, threshold) {
    return new Chartist.Line(targetElementId, data, {
        showArea: true,
        axisX: {
            labelInterpolationFnc: function (value) {
                return value;
            }
        },
        plugins: [
            Chartist.plugins.tooltip(),
            Chartist.plugins.ctThreshold({
                threshold: threshold
            })
        ]
    });
}

var now = new Date();
var current = now;

$(document).ready(function () {
    getCriterias();
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if (detailChart) {
            detailChart.update();
        }
    });

});