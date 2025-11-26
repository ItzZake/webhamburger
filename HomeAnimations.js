// HomeAnimations.js

// 1) Fade / slide / zoom animations (AOS-like, replay on scroll)
function handleScrollAnimations() {
    $('.fade-right, .fade-left, .fade-up, .fade-down, .zoom-in').each(function () {
        var $el = $(this);

        var elementTop = $el.offset().top;
        var elementBottom = elementTop + $el.outerHeight();

        var scrollTop = $(window).scrollTop();
        var windowHeight = $(window).height();
        var viewportBottom = scrollTop + windowHeight;

        var offset = 0;
        if ($el.hasClass('offset-300')) offset = 300;

        // ENTER viewport → add in-view
        if (viewportBottom - offset > elementTop && scrollTop < elementBottom + offset) {
            $el.addClass('in-view');
        } else {
            // EXIT viewport → remove in-view so it can replay
            $el.removeClass('in-view');
        }
    });
}

// 2) Scroll-linked translation for .scroll-shift (card)
function handleScrollShift() {
    var scrollTop = $(window).scrollTop();

    $('.scroll-shift').each(function () {
        var $el = $(this);
        var elementTop = $el.offset().top;

        // Top of viewport triggers
        // Start moving when top of viewport is 300px ABOVE the card
        // Stop increasing when top of viewport is 300px BELOW the card
        var start = elementTop - 500; // scrollTop here → progress = 0
        var end   = elementTop + 500; // scrollTop here → progress = 1

        var progress;

        if (scrollTop <= start) {
            progress = 0;
        } else if (scrollTop >= end) {
            progress = 1;
        } else {
            progress = (scrollTop - start) / (end - start); // 0 → 1 within 300px range
        }

        // Max upward movement (how far card moves up)
        var maxShift = -150; // px (make -300 if you want more)

        var translateY = maxShift * progress;

        // Apply transform (card moves up as you scroll down, down as you scroll up)
        $el.css('transform', 'translateY(' + translateY + 'px)');
    });
}

// Hook everything up
$(function () {
    function onScroll() {
        handleScrollAnimations();
        handleScrollShift();
    }

    // Run on load + on scroll
    onScroll();
    $(window).on('scroll', onScroll);
    $(window).on('resize', onScroll);
});
