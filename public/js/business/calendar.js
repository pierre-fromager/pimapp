
var attrDuration = 'data-duration';
var attrStatus = 'data-status';
var daysInMonth = 0;
var businessDays = 0;

$('document').ready(function () {

    function setMessage(message, error) {
        var target = document.getElementById('cal_message');
        target.innerHTML = message;
        var className = (error === true) ? 'label-danger' : 'label-success';
        target.className = 'label ' + className;
        target.style['display'] = 'inline-table';
        setTimeout(function () {
            target.style['display'] = 'none';
        }, 1500);
    }
    
    function setDaysCount() {
        var target = document.getElementById('dayscount');
        var total = cells.length;
        var amount = 0;
        for (c = 0; c < total; ++c) {
            amount += parseFloat(cells[c].getAttribute(attrDuration));
        }
        daysInMonth = total;
        businessDays = amount;
        target.innerHTML = '( ' + amount + ' / ' + total + ' )';
    }

    function postDatas(datas) {
        $.ajax({
            url: ROOT_URL + 'business/calupdate',
            data: {
                month: month,
                year: year,
                uid: uid,
                caldatas: datas,
            },
            method: 'POST'
        }).fail(function () {
        }).always(function () {
        }).done(function (dataResponse) {
            document.getElementById('ajax').style['display'] = 'none';
            var error = (dataResponse.error === true);
            var message = (error) ? 'Failed' : 'Success';
            setMessage(message, error);
        });
    }

    function getDatas() {
        document.getElementById('ajax').style['display'] = 'block';
        $.ajax({
            url: ROOT_URL + 'business/caljson',
            data: {
                uid: uid,
                month: month,
                year: year
            },
            method: 'POST'
        }).fail(function () {
        }).always(function () {
        }).done(function (data) {
            for (c = 0; c < data.result.length; ++c) {
                var item = data.result[c];
                var id = item.datee.substr(0, 10);
                var duration = parseFloat(item.duration);
                var status = parseInt(item.status);
                var target = document.getElementById(id);
                target.setAttribute(attrDuration, duration);
                target.setAttribute(attrStatus, status);
                target.style.background = getBgColorStatus(status);
                target.style.color = getColorStatus(status);
                var dayNum = parseInt(item.datee.substr(8, 2));
                target.innerHTML = dayNum + '<sub>' + target.getAttribute(attrDuration) 
                    + '</sub>';
            }
            document.getElementById('ajax').style['display'] = 'none';
            setDaysCount();
        });
    }
    
    function mailIformer() {
        //var summary = document.getElementById('dayscount').value;
        $.ajax({
            url: ROOT_URL + 'mail/sendapproval',
            data: {
                month: month,
                year: year,
                uid: uid,
                dmonth:daysInMonth,
                bdays:businessDays,
                //summary: summary
            },
            method: 'POST'
        }).fail(function () {
        }).always(function () {
        }).done(function (dataResponse) {
            console.log('mailIformer dataResponse ',dataResponse);
            /*
            document.getElementById('ajax').style['display'] = 'none';
            var error = (dataResponse.error === true);
            var message = (error) ? 'Failed' : 'Success';
            setMessage(message, error);*/
        });
    }
    
    function getBgColorStatus(statusCode) {
        // CODE_WAITING, CODE_APPROVED, CODE_REFUSED, CODE_BILLED
        colors = ['#DDD', '#5cb85c', '#eea236', '#337ab7'];
        return colors[statusCode - 1];
    }

    function getColorStatus(statusCode) {
        colors = ['black', 'white', 'white', 'white'];
        return colors[statusCode - 1];
    }

    function setCells() {
        cells = document.getElementsByClassName('bd');
    }

    var cellClick = function () {
        var duration = this.getAttribute(attrDuration);
        var statusCode = this.getAttribute(attrStatus);
        var posValue = cellValues.indexOf(duration);
        if (posValue > -1 && canModify(statusCode)) {
            posValue = (posValue === 2) ? -1 : posValue;
            var dayNum = this.childNodes[0].nodeValue;
            var newCellValue = cellValues[posValue + 1];
            this.setAttribute(attrDuration, newCellValue);
            this.innerHTML = dayNum + '<sub>' + this.getAttribute(attrDuration) + '</sub>';
        }
        setDaysCount();
    };
    
    function canModify(statusCode, message = ''){
        // either awaiting or refused
        var isClickable = (statusCode == 1 || statusCode == 3);
        if (!isClickable && message) {
            alert(message);
        }
        return isClickable;
    }

    function bindCells() {
        for (var i = 0; i < cells.length; i++) {
            var id = cells[i].getAttribute('id');
            var target = document.getElementById(id);
            target.addEventListener('click', cellClick);
        }
    }

    document.getElementById('check').addEventListener('click',
            function () {
                for (var i = 0; i < cells.length; i++) {
                    var dayNum = cells[i].childNodes[0].nodeValue;
                    var id = cells[i].getAttribute('id');
                    var target = document.getElementById(id);
                    if (canModify(target.getAttribute(attrStatus))) {
                        target.setAttribute(attrDuration, 1);
                        target.setAttribute(attrStatus, waitingStatus);
                        var duration = target.getAttribute(attrDuration);
                        target.innerHTML = dayNum + '<sub>' + duration + '</sub>';
                    }
                }
                setCells();
                setDaysCount();
            }
    );

    document.getElementById('valid').addEventListener('click',
        function () {
            document.getElementById('ajax').style['display'] = 'block';
            setCells();
            var toPost = {
                size: cells.length,
                datas: []
            };
            for (var i = 0; i < cells.length; i++) {
                toPost.datas.push({
                    datee: cells[i].getAttribute('id'),
                    duration: parseFloat(cells[i].getAttribute(attrDuration)),
                    status: parseInt(cells[i].getAttribute(attrStatus)),
                });
            }
            postDatas(toPost);
            mailIformer();
        }
    );

    setCells();
    getDatas();
    bindCells();

});