$('document').ready(function () {

    $('.block-profil').hover(
            function () {
                $(this).children('img').before(
                        '<span class="zoomer orangeColor upper">'
                        + 'Cliquer pour zoomer'
                        + '</span>'
                        );
                $('.zoomer').fadeIn(200);
            },
            function () {
                $('.zoomer').fadeOut(200);
                $('.zoomer').remove();
            }
    );

    $('.photo-profil').click(function () {
        var that = $(this);
        var url = that.attr('src');
        var html = '<img class="modal-photo-profil" src="' + url + '"></img>';
        $('#modal-body').html(html);
        $('#modal').modal('show');
    });

    $j('button').on('click', function (e) {
        var dataLink = $j(this).attr('data-link');
        if (typeof dataLink !== typeof undefined && dataLink !== false) {
            window.location.href = dataLink;
        }
    });

    function stickyFooter() {
        var docHeight = $(window).height();
        var footerHeight = $('footer').height();
        var footerTop = $('footer').position().top + footerHeight;
        if (footerTop < docHeight) {
            $('footer').css('margin-top', (docHeight - footerTop) - 70 + 'px');
        }
    }

    stickyFooter();
    
    $(window).resize(function () {
        stickyFooter();
    });
    
    if (jQuery().chosen) {
        $j('.chosen').chosen();
    }
    
    if (jQuery().datepicker) {
        $j('.datepicker').datepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            weekStart: 1
        }).on('changeDate', function (ev) {
            if (ev.viewMode === 'days') {
                $(this).datepicker('hide');
            }
        });
    }
});