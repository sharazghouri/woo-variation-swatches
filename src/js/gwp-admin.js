(function ($) {

    import('./GWPAdminHelper').then(({GWPAdminHelper}) => {

        $.fn.gwp_live_feed = function () {
            GWPAdminHelper.LiveFeed();
        }

        $.fn.gwp_deactivate_popup = function ($slug) {
            GWPAdminHelper.DeactivatePopup($slug);
        }
    });

}(jQuery));