function windowPopup(url, width, height) {
    var left = (window.innerWidth / 2) - (width / 2);
    var top = (window.innerHeight / 2) - (height / 2);
    window.open(
        url,
        "",
        "menubar=no,toolbar=no,resizable=yes,scrollbars=yes,width="
        + width + ",height=" + height + ",top=" + top
        + ",left=" + left
    );
}

$(document).ready(function () {
    
    $('#facebookShare').click(function (e) {
        var el = $(this);
        e.preventDefault();
        var baseUrl = el.attr('data-baseurl');
        var dataUrl = (ROOT_URL) ? ROOT_URL : el.attr('data-url');
        var url = baseUrl + encodeURIComponent(dataUrl);
        windowPopup(url, 450, 300);
    });

    $('#twitterShare').click(function (e) {
        var el = $(this);
        e.preventDefault();
        var baseUrl = el.attr('data-baseurl');
        var dataText = el.attr('data-text');
        var dataUrl = (el.attr('data-url')) ? el.attr('data-url') : ROOT_URL ;
        var dataHashtag = el.attr('data-hashtag');
        var url = baseUrl + 'text=' + dataText + '&url='
            + encodeURIComponent(dataUrl) + '&hashtags=' + dataHashtag;
        windowPopup(url, 450, 300);
    });
    
});