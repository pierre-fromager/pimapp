$j(document).ready(function () {
    var tableId = $j('.managetable').attr('id');
    if (tableId) {
        var tableSelector = '#' + tableId;
        $j(tableSelector).columnManager({
            listTargetID: 'targetall'
            , onClass: 'tableColOn'
            , offClass: 'tableColOff'
            , saveState: true
        });

        var opt = {
            listTargetID: 'targetall'
            , onClass: 'tableColOn'
            , offClass: 'tableColOff'
            , hide: function (c) {
                $j(c).hide();
            }
            , show: function (c) {
                $j(c).show();
            }
        };
        console.log(tableSelector);
        var numCols = $j(tableSelector).find('tr')[0].cells.length;
        var visibleCols = 5;
        var currentIndex = 0;
        var start = 0;
        var end = 0;

        // Force display action from start
        $j('th.theader.action').css('display', 'block');
        $j('td.table-action').each(function (index) {
            $j(this).css('display', 'block');
        });
        hideAllColunms();
        showColunms(start, visibleCols);
    }

    $j('#moreAction').click(function () {
        ++currentIndex;
        start = currentIndex * visibleCols;
        end = start + visibleCols;
        $j('#moreAction').removeClass('disabled');
        $j('#lessAction').removeClass('disabled');
        if (end >= numCols) {
            end = numCols;
            $j('#moreAction').addClass('disabled');
        }
        start = currentIndex * visibleCols;
        hideAllColunms(opt);
        showColunms(start, end);
        if (hasAction()) {
            $j(tableSelector).showColumns(numCols, opt);
        }
        updateChosen();
    });

    $j('#lessAction').click(function () {
        --currentIndex;
        start = currentIndex * visibleCols;
        end = start + visibleCols;
        $j('#lessAction').removeClass('disabled');
        if (currentIndex <= 0) {
            $j('#lessAction').addClass('disabled');
            currentIndex = 0;
            start = currentIndex * visibleCols;
            end = start + visibleCols;
        }
        if (end < numCols) {
            $j('#moreAction').removeClass('disabled');
        }
        hideAllColunms(opt);
        showColunms(start, end);
        if (hasAction()) {
            $j(tableSelector).showColumns(numCols, opt);
        }
        updateChosen();
    });

    if (!hasColumnCookie()) {
        hideAllColunms(opt);
        showColunms(currentIndex, visibleCols);
    }

    function hideAllColunms(opt) {
        var arrColumns = getArrayColumns(0, numCols);
        $j(tableSelector).hideColumns(arrColumns, opt);
    }

    function showColunms(start, end) {
        var arrColumns = getArrayColumns(start, end);
        $j(tableSelector).showColumns(arrColumns, opt);
    }

    function getArrayColumns(start, end) {
        var arr = [];
        while (start <= end) {
            arr.push(start++);
        }
        return arr;
    }

    function hasColumnCookie() {
        var columnCookieName = 'columnManagerC' + tableId;
        var hasColumnCookie = (jQuery.cookie(columnCookieName) !== null);
        return hasColumnCookie;
    }

    function hasAction() {
        var lastThContent = $j(tableSelector + ' th:last a').html().substr(0, 7);
        var hasAction = (lastThContent == 'Actions');
        return hasAction;
    }

    //**** RECHERCHE ******//
    $j('#toogle-filtrer').on('click', function (event) {
        //if($j('.chosen-container').length==0){
        $j('#test').chosen().change(function (evt, params) {
            var selected = $j('select#test>option:selected');
            var tableId = $j('.managetable').attr('id');

            // masque tout
            $j('#' + tableId + ' tr th').css('display', 'none');
            $j('#' + tableId + ' tr td').css('display', 'none');

            // affiche le nécessaire
            $j.each(selected, function (index, value) {
                var row = $j(value).attr('data-row');
                $j('#' + tableId + ' th:eq(' + row + ')').css('display', '');
                $j.each($j('#' + tableId + ' tr'), function (i, v) {
                    $j('#' + tableId + ' tr:eq(' + i + ') td:eq(' + row + ')').css('display', '');
                });
            });

            // sauvegarde
            var table = $j('#' + tableId + ' th');
            var cookieName = 'columnManagerC';
            var cookieString = '';
            $j.each(table, function (index, value) {
                var td = $j(value);
                if ($j(td).css('display') == 'none') {
                    cookieString = cookieString + '0';
                } else {
                    cookieString = cookieString + '1';
                }
            });
            $j.cookie(cookieName + tableId, cookieString, {expires: 9999});
        });
        //}
        if ($j('#filtrer').hasClass('open')) {
            $j('#toogle-filtrer').removeClass('open');
            $j('#filtrer').removeClass('open');
            $j('#filtrer').removeClass('overflow');

        } else {
            $j('#toogle-filtrer').addClass('open');
            $j('#filtrer').addClass('open');
            setTimeout(function () {
                $j('#filtrer').addClass('overflow');
            }, 100);
        }
    });

    function updateChosen() {
        var cookieName = 'columnManagerC';
        var tableId = $j('.managetable').attr('id');
        var cookie = $j.cookie(cookieName + tableId);
        var select = $j('select#test>option');
        $j.each(select, function (i, value) {
            if (cookie.substr(i, 1) == '1') {
                $j('select#test>option:eq(' + i + ')').prop('selected', true);
            } else {
                $j('select#test>option:eq(' + i + ')').prop('selected', false);
            }
        });
        $j('#test').trigger('chosen:updated');
    }
});

