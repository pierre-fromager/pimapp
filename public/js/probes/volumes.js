// https://www.smashingmagazine.com/2014/12/chartist-js-open-source-library-responsive-charts/
// https://gionkunz.github.io/chartist-js/plugins.html#axis-title-plugin
// https://github.com/gionkunz/chartist-plugin-threshold
// https://gionkunz.github.io/chartist-js/api-documentation.html

var mChart = dChart = null;

function getMonthlyTransfo(data) {
    var _labels = [];
    var _avgSeries = [];
    var _minSeries = [];
    var _maxSeries = [];
    var ratio = 1000;
    for (c = 0; c < data.length; ++c) {
        _labels.push(data[c]['day'].replace('2018-',''));
        _avgSeries.push(getChartPoint(
            parseInt(data[c]['avgvalue']) / ratio, 'avgDaylyProbe', 'Moyenne'
        ));
        _minSeries.push(getChartPoint(
            parseInt(data[c]['minvalue']) / ratio, 'minDaylyProbe', 'Minimum'
        ));
        _maxSeries.push(getChartPoint(
            parseInt(data[c]['maxvalue']) / ratio, 'maxDaylyProbe', 'Maximum'
        ));
    }
    return {series: [_avgSeries, _minSeries, _maxSeries], labels: _labels};
}

function getDailyTransfo(data) {
    var _labels = [];
    var _avgSeries = [];
    var _minSeries = [];
    var _maxSeries = [];
    var ratio = 1000;
    for (c = 0; c < data.length; ++c) {
        _labels.push(data[c]['hour']);
        _avgSeries.push(getChartPoint(
            parseInt(data[c]['_avg']) / ratio, 'avgDaylyProbe', 'Moyenne'
        ));
        _minSeries.push(getChartPoint(
            parseInt(data[c]['_min']) / ratio, 'minDaylyProbe', 'Minimum'
        ));
        _maxSeries.push(getChartPoint(
            parseInt(data[c]['_max']) / ratio, 'maxDaylyProbe', 'Maximum'
        ));
    }
    return {series: [_avgSeries, _minSeries, _maxSeries], labels: _labels};
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
        getMonthlyDatas(criterias);
        getLastHours(criterias);
    });
}

function getLastHours(criterias, urlAction) {
    $.ajax({
        url: ROOT_URL + "probes/voldayjson",
        method: 'POST',
        data: {pattern: criterias},
        dataType: "json",
        success: function (data) {
            dChart = drawLineChart(
                '#daily', 
                getDailyTransfo(data),
                30
            );
        },
        error: function (data) {
            console.log('Error getProbeDatas', data);
        }
    });
}

function getMonthlyDatas(criterias, urlAction) {
    urlAction = (!!urlAction)
            ? urlAction
            : "probes/voljson";
    $.ajax({
        url: ROOT_URL + "probes/voljson/year/2018/month/03",
        method: 'POST',
        data: {pattern: criterias},
        dataType: "json",
        success: function (data) {
            mChart = drawLineChart(
                '#monthly', 
                getMonthlyTransfo(data),
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
            onlyInteger: true,/*
            labelInterpolationFnc: function (value) {
                return value;
            }*/
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
    $j('#toogle-filtrer').hide();

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        console.log('EVEVENT ', e);
        if (mChart && dChart) {
            mChart.update();
            dChart.update();
        }
    });

});