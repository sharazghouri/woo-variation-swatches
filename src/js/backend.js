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

        $('.wp-list-table.plugins').find('[data-slug="woo-variation-swatches"].active').each(function () {
            let slug           = $(this).data('slug');
            let plugin         = $(this).data('plugin');
            let deactivate_url = $(this).find('.deactivate a').prop('href');

            $('#gwp-plugin-slug').val(slug);
            $('#feedback-dialog-form-button-skip').prop('href', deactivate_url)
            $('#feedback-dialog-form-button-send').on('click', (event) => {
                event.preventDefault();
                let data = $('#gwp-plugin-deactivate-feedback-dialog-wrapper .feedback-dialog-form').serializeJSON();

                console.log(data)
            })

            $('#gwp-plugin-deactivate-feedback-dialog-wrapper :radio').on('change', function () {

                $(this).closest('.feedback-dialog-form-body').find(':text').prop('disabled', true).hide();

                $(this).nextAll(':text').prop('disabled', false).show().focus();
                // console.log($(this).val())
            })

            $(this).find('.deactivate a').on('click', (event) => {
                event.preventDefault();
                $('#gwp-plugin-deactivate-feedback-dialog-wrapper').dialog('open');
            });

            $('#gwp-plugin-deactivate-feedback-dialog-wrapper').dialog({
                title         : 'Quick Feedback',
                dialogClass   : 'wp-dialog gwp-deactivate-feedback-dialog',
                autoOpen      : false,
                draggable     : false,
                width         : 'auto',
                modal         : true,
                resizable     : false,
                closeOnEscape : true,
                position      : {
                    my : "center",
                    at : "center",
                    of : window
                },
                create        : function () {
                    $('.ui-dialog-titlebar-close').addClass('ui-button');
                },
                open          : function () {
                    $('.ui-widget-overlay').bind('click', function () {
                        $('#gwp-plugin-deactivate-feedback-dialog-wrapper').dialog('close');
                    })
                }
            });

        });
    });
});  // end of jquery main wrapper