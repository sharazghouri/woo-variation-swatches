jQuery($ => {
    import('./WooVariationSwatches').then(() => {
        // Init on Ajax Popup :)
        $(document).on('wc_variation_form', '.variations_form', function () {
            $(this).WooVariationSwatches();
        });

        // Support for Jetpack's Infinite Scroll,
        $(document.body).on('post-load', function () {
            $('.variations_form').wc_variation_form();
        });

    });
});  // end of jquery main wrapper