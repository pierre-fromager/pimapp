
function getNextMonth() {
    var cm = new Date(current).getMonth();
    return (cm + 1) % 12 + 1;
}

function getPreviousMonth() {
    var d = new Date(current);
    d.setDate(1);
    d.setMonth(d.getMonth() - 1);
    return (d.getMonth() == 0) ? 1 : d.getMonth() + 1;
}

function padDigitss(number, digits) {
    return Array(Math.max(digits - String(number).length + 1, 0)).join(0) + number;
}
/*
function reverseText(query) {
    query = (query) ? query : 'div.p';
    var space = '&nbsp;';
    var spaceL = ' ';
    var pList = document.querySelectorAll(query), i;
    for (i = 0; i < pList.length; ++i) {
        var childs = pList[i].childNodes;
        if (childs.length === 1) {
            var tContent = pList[i].innerHTML;
            pList[i].innerHTML = '';
            for (var ct = 0; ct < tContent.length; ++ct) {
                var div = document.createElement('div');
                div.innerHTML = (tContent[ct] === spaceL) ? space : tContent[ct];
                div.className = 'lefti';
                pList[i].appendChild(div);
            }
            var childss = pList[i].childNodes;
            for (var ct = 0; ct < childss.length; ++ct) {
                setTimeout(
                    function (x) {
                        return function () {
                            childss[x].className = 'rt';
                        };
                    }(ct)
                    , (1 + ct) * childss[ct].innerHTML.charCodeAt(0)
                );
            }
            pList[i].className += ' rd_done';
        } else {
            var tContent = '';
            for (var ct = 0; ct < childs.length; ++ct) {
                tContent += (childs[ct].innerHTML === space)
                    ? spaceL
                    : childs[ct].innerHTML;
            }
            for (var ct = 0; ct < childs.length; ++ct) {
                pList[i].removeChild(childs[ct]);
            }
            pList[i].innerHTML = tContent;
        }
    }
}
*/