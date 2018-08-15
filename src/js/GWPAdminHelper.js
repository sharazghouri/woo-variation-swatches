/*global GWPAdmin*/

const GWPAdminHelper = (($) => {
    class GWPAdminHelper {

        static LiveFeed() {
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
        }

        static ResetPopupData() {

            let $button = $('#feedback-dialog-form-button-send');
            $button.prop('disabled', false).text($button.data('defaultvalue')).next().removeClass('visible');
        }

        static DeactivatePopup(pluginslug) {

            $('#gwp-plugin-deactivate-feedback-dialog-wrapper').dialog({
                title         : GWPAdmin.feedback_title,
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
                    });

                    let opener = $(this).data('gwp-deactivate-dialog-opener');

                    GWPAdminHelper.ResetPopupData();

                    let slug            = $(opener).data('slug');
                    let plugin          = $(opener).data('plugin');
                    let deactivate_link = $(opener).data('deactivate_link');

                    $('#gwp-plugin-slug').val(slug);
                    $('#feedback-dialog-form-button-skip').prop('href', deactivate_link)
                    $('#feedback-dialog-form-button-send').data('deactivate_link', deactivate_link)

                }
            });

            $('#feedback-dialog-form-button-send').on('click', function (event) {
                event.preventDefault();
                let data = $('#gwp-plugin-deactivate-feedback-dialog-wrapper .feedback-dialog-form').serializeJSON();

                let link = $(this).data('deactivate_link');

                if (typeof data['reason_type'] === 'undefined') {
                    return;
                }

                $(this).prop('disabled', true).text($(this).data('deactivating')).next().addClass('visible');

                wp.ajax.send(data.action, {
                    data,
                    success : (response) => {
                        window.location.replace(link)
                    },
                    error   : () => {
                        window.location.replace(link)
                    }
                });

                //console.log(data)
            });

            $('#gwp-plugin-deactivate-feedback-dialog-wrapper :radio').on('change', function () {

                $(this).closest('.feedback-dialog-form-body').find('.feedback-text').prop('disabled', true).hide();

                $(this).nextAll('.feedback-text').prop('disabled', false).show().focus();
                // console.log($(this).val())
            });

            $('.wp-list-table.plugins').find('[data-slug="' + pluginslug + '"].active').each(function () {

                let deactivate_link = $(this).find('.deactivate a').prop('href');

                $(this).data('deactivate_link', deactivate_link);

                $(this).find('.deactivate a').on('click', (event) => {
                    event.preventDefault();

                    $('#gwp-plugin-deactivate-feedback-dialog-wrapper').data('gwp-deactivate-dialog-opener', this).dialog('open');
                });
            });
        }
    }

    return GWPAdminHelper;
})(jQuery);

export { GWPAdminHelper };