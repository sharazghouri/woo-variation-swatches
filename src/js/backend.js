jQuery($ => {
    import('./PluginHelper').then(({PluginHelper}) => {
        PluginHelper.SelectWoo();
        PluginHelper.ColorPicker();
        PluginHelper.FieldDependency();
        PluginHelper.ImageUploader();
        PluginHelper.AttributeDialog();
        $(document.body).on('woocommerce_added_attribute', function () {
            PluginHelper.SelectWoo();
            PluginHelper.ColorPicker();
            PluginHelper.ImageUploader();
            PluginHelper.AttributeDialog();
        });

        $(document.body).on('wvs_pro_product_swatches_variation_loaded', () => {
            PluginHelper.ColorPicker();
            PluginHelper.ImageUploader();
        })

        $('.gwp-live-feed-close').on('click', function (e) {
            e.preventDefault();
            let id = $(this).data('feed_id');
            wp.ajax.send('gwp_live_feed_close', {
                data : {id}
            });

            $(this).parent().fadeOut('fast', function () {
                $(this).remove()
            })
        });
    });
});  // end of jquery main wrapper