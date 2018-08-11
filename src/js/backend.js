jQuery($ => {
    import('./PluginHelper').then(({PluginHelper}) => {

        PluginHelper.GWPAdmin();
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
        });
    });
});  // end of jquery main wrapper