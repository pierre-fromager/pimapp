(function ($) {
    $.fn.autogrow = function (options) {
        var $this, minHeight, lineHeight, shadow, update;
        this.filter('textarea').each(function () {
            $this = $(this);
            minHeight = $this.height();
            lineHeight = $this.css('lineHeight');
            $this.css('overflow','hidden');
            shadow = $('<div></div>').css({
                position: 'absolute',
                'word-wrap': 'break-word',
                top: -10000,
                left: -10000,
                width: $this.width(),
                fontSize: $this.css('fontSize'),
                fontFamily: $this.css('fontFamily'),
                lineHeight: $this.css('lineHeight'),
                resize: 'none'
            }).appendTo(document.body);
            update = function () {
                shadow.css('width', $(this).width());
                var val = this.value.replace(/&/g, '&amp;')
                                    .replace(/</g, '&lt;')
                                    .replace(/>/g, '&gt;')
                                    .replace(/\n/g, '<br/>')
                                    .replace(/\s/g,'&nbsp;');
                if (val.indexOf('<br/>', val.length - 5) !== -1) { val += '#'; }
                shadow.html(val);
                $(this).css('height', Math.max(shadow.height(), minHeight));
            };
            $this.change(update).keyup(update).keydown(update);
            update.apply(this);
        });
        return this;
    };
}(jQuery));