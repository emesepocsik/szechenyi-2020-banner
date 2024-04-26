(function ($) {
    $('.sz2020').each(function () {
        var container = $(this);
        var index = container.index('.sz2020');
        var reopenDelayDays = container.data('open-delay') || 1;
        var displayDelayMilliseconds = (container.data('display-delay') || 0) * 1000;
        var reopenDelay = reopenDelayDays * 24 * 60 * 60 * 1000;
        var currentTime = new Date().getTime();
        var closeTimeKey = 'pops-close-time-' + index;
        var closeTime = localStorage.getItem(closeTimeKey);
        function showPops() {
            if (!closeTime || currentTime - closeTime >= reopenDelay) {
                setTimeout(function () {
                    container.fadeIn();
                }, displayDelayMilliseconds);
            }
        }
        showPops();
        container.on('click', '.sz2020-close', function () {
            container.fadeOut();
            localStorage.setItem(closeTimeKey, currentTime);
        });
    });
})(jQuery);