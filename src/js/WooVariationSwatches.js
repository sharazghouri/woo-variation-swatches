// ================================================================
// WooCommerce Variation Change
// ================================================================

const WooVariationSwatches = (($) => {

    const Default = {};

    class WooVariationSwatches {

        constructor(element, config) {

            // Assign
            this._element           = $(element);
            this._config            = $.extend({}, Default, config);
            this._generated         = {};
            this._out_of_stock      = {};
            this.product_variations = this._element.data('product_variations');
            this.is_ajax_variation  = !this.product_variations;
            this.product_id         = this._element.data('product_id');
            this.hidden_behaviour   = $('body').hasClass('woo-variation-swatches-attribute-behavior-hide');
            this.is_mobile          = $('body').hasClass('woo-variation-swatches-on-mobile');

            // Call
            this.init(this.is_ajax_variation, this.hidden_behaviour);
            this.loaded(this.is_ajax_variation, this.hidden_behaviour);
            this.update(this.is_ajax_variation, this.hidden_behaviour);
            this.reset(this.is_ajax_variation, this.hidden_behaviour);

            // Trigger
            $(document).trigger('woo_variation_swatches', [this._element]);
        }

        static _jQueryInterface(config) {
            return this.each(function () {
                new WooVariationSwatches(this, config)
            })
        }

        init(is_ajax, hidden_behaviour) {

            let _this = this;
            this._element.find('ul.variable-items-wrapper').each(function (i, el) {

                let select         = $(this).siblings('select.woo-variation-raw-select');
                let li             = $(this).find('li');
                let reselect_clear = $(this).hasClass('reselect-clear');
                let is_mobile      = $('body').hasClass('woo-variation-swatches-on-mobile');

                $(this).parent().addClass('woo-variation-items-wrapper');

                // For Avada FIX
                if (select.length < 1) {
                    select = $(this).parent().find('select.woo-variation-raw-select');
                }

                if (reselect_clear) {
                    $(this).on('touchstart click', 'li:not(.selected):not(.radio-variable-item)', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        let value = $(this).data('value');
                        select.val(value).trigger('change');
                        select.trigger('click');

                        select.trigger('focusin');

                        if (is_mobile) {
                            select.trigger('touchstart');
                        }

                        $(this).trigger('focus'); // Mobile tooltip
                        $(this).trigger('wvs-selected-item', [value, select, _this._element]); // Custom Event for li
                    });

                    $(this).on('touchstart click', 'li.selected:not(.radio-variable-item)', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        select.val('').trigger('change');
                        select.trigger('click');

                        select.trigger('focusin');

                        if (is_mobile) {
                            select.trigger('touchstart');
                        }

                        $(this).trigger('focus'); // Mobile tooltip

                        $(this).trigger('wvs-unselected-item', [value, select, _this._element]); // Custom Event for li

                    });

                    // RADIO
                    $(this).on('touchstart click', 'input.wvs-radio-variable-item:radio', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).trigger('change');
                    });

                    $(this).on('change', 'input.wvs-radio-variable-item:radio', function (e) {
                        e.preventDefault();
                        e.stopPropagation();

                        let value = $(this).val();

                        if ($(this).parent('li.radio-variable-item').hasClass('selected')) {
                            select.val('').trigger('change');
                            _.delay(() => {
                                $(this).prop('checked', false);
                                $(this).parent('li.radio-variable-item').trigger('wvs-unselected-item', [value, select, _this._element]); // Custom Event for li
                            }, 1)
                        }
                        else {
                            select.val(value).trigger('change');
                            $(this).parent('.radio-variable-item').trigger('wvs-selected-item', [value, select, _this._element]); // Custom Event for li
                        }

                        select.trigger('click');
                        select.trigger('focusin');
                        if (is_mobile) {
                            select.trigger('touchstart');
                        }
                    });
                }
                else {
                    $(this).on('touchstart click', 'li:not(.radio-variable-item)', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        let value = $(this).data('value');
                        select.val(value).trigger('change');
                        select.trigger('click');
                        select.trigger('focusin');
                        if (is_mobile) {
                            select.trigger('touchstart');
                        }

                        $(this).trigger('focus'); // Mobile tooltip

                        $(this).trigger('wvs-selected-item', [value, select, _this._element]); // Custom Event for li
                    });

                    // Radio
                    $(this).on('change', 'input.wvs-radio-variable-item:radio', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        let value = $(this).val();

                        select.val(value).trigger('change');
                        select.trigger('click');
                        select.trigger('focusin');

                        if (is_mobile) {
                            select.trigger('touchstart');
                        }

                        // Radio
                        $(this).parent('li.radio-variable-item').removeClass('selected disabled').addClass('selected');
                        $(this).parent('li.radio-variable-item').trigger('wvs-selected-item', [value, select, _this._element]); // Custom Event for li
                    });
                }
            });

            _.delay(() => {
                this._element.trigger('woo_variation_swatches_init', [this, this.product_variations])
                $(document).trigger('woo_variation_swatches_loaded', [this._element, this.product_variations])
            }, 2)
        }

        loaded(is_ajax, hidden_behaviour) {
            if (!is_ajax) {
                this._element.on('woo_variation_swatches_init', function (event, object, product_variations) {

                    object._generated = product_variations.reduce((obj, variation) => {

                        Object.keys(variation.attributes).map((attribute_name) => {
                            if (!obj[attribute_name]) {
                                obj[attribute_name] = []
                            }

                            if (variation.attributes[attribute_name]) {
                                obj[attribute_name].push(variation.attributes[attribute_name]);
                            }
                        });

                        return obj;

                    }, {});

                    object._out_of_stock = product_variations.reduce((obj, variation) => {

                        Object.keys(variation.attributes).map((attribute_name) => {
                            if (!obj[attribute_name]) {
                                obj[attribute_name] = []
                            }

                            if (variation.attributes[attribute_name] && !variation.is_in_stock) {
                                obj[attribute_name].push(variation.attributes[attribute_name]);
                            }
                        });

                        return obj;

                    }, {});

                    // console.log(object._out_of_stock);

                    $(this).find('ul.variable-items-wrapper').each(function () {
                        let li                  = $(this).find('li');
                        let attribute           = $(this).data('attribute_name');
                        let attribute_values    = object._generated[attribute];
                        let out_of_stock_values = object._out_of_stock[attribute];

                        //console.log(out_of_stock_values)

                        li.each(function () {
                            let attribute_value = $(this).attr('data-value');

                            if (!_.isEmpty(attribute_values) && !attribute_values.includes(attribute_value)) {
                                $(this).removeClass('selected');
                                $(this).addClass('disabled');

                                if ($(this).hasClass('radio-variable-item')) {
                                    $(this).find('input.wvs-radio-variable-item:radio').prop('disabled', true).prop('checked', false);
                                }
                            }
                        });
                    });
                });
            }
        }

        reset(is_ajax, hidden_behaviour) {
            let _this = this;
            this._element.on('reset_data', function (event) {
                $(this).find('ul.variable-items-wrapper').each(function () {
                    let li = $(this).find('li');
                    li.each(function () {
                        if (!is_ajax) {
                            $(this).removeClass('selected disabled');

                            if ($(this).hasClass('radio-variable-item')) {
                                $(this).find('input.wvs-radio-variable-item:radio').prop('disabled', false).prop('checked', false);
                            }
                        }
                        else {
                            if ($(this).hasClass('radio-variable-item')) {
                                //    $(this).find('input.wvs-radio-variable-item:radio').prop('checked', false);
                            }
                        }

                        $(this).trigger('wvs-unselected-item', ['', '', _this._element]); // Custom Event for li
                    });
                });
            });
        }

        update(is_ajax, hidden_behaviour) {

            this._element.on('__found_variation', (event, variation) => {


                //console.log(this.$attributeFields);

                /*  _.delay(() => {
                      $(this).find('ul.variable-items-wrapper').each(function () {
                          let attribute_name = $(this).data('attribute_name');

                          $(this).find('li').each(function () {
                              let value = $(this).attr('data-value');

                              console.log(variation)

                              if (variation.attributes[attribute_name] === value && !variation.is_in_stock) {
                                  $(this).addClass('disabled');
                              }

                          });
                      });

                  }, 2)*/
            });

            this._element.on('woocommerce_variation_has_changed', function (event) {
                if (is_ajax) {
                    $(this).find('ul.variable-items-wrapper').each(function () {
                        let selected = '',
                            options  = $(this).siblings('select.woo-variation-raw-select').find('option'),
                            current  = $(this).siblings('select.woo-variation-raw-select').find('option:selected'),
                            eq       = $(this).siblings('select.woo-variation-raw-select').find('option').eq(1),
                            li       = $(this).find('li'),
                            selects  = [];

                        // For Avada FIX
                        if (options.length < 1) {
                            options = $(this).parent().find('select.woo-variation-raw-select').find('option');
                            current = $(this).parent().find('select.woo-variation-raw-select').find('option:selected');
                            eq      = $(this).parent().find('select.woo-variation-raw-select').find('option').eq(1);
                        }

                        options.each(function () {
                            if ($(this).val() !== '') {
                                selects.push($(this).val());
                                selected = current ? current.val() : eq.val();
                            }
                        });

                        _.delay(() => {
                            li.each(function () {
                                let value = $(this).attr('data-value');
                                $(this).removeClass('selected disabled');

                                if (value === selected) {
                                    $(this).addClass('selected');
                                    if ($(this).hasClass('radio-variable-item')) {
                                        $(this).find('input.wvs-radio-variable-item:radio').prop('disabled', false).prop('checked', true);
                                    }
                                }
                            });

                            // Items Updated
                            $(this).trigger('wvs-items-updated');
                        }, 1);
                    });
                }
            });

            // WithOut Ajax Update
            this._element.on('woocommerce_update_variation_values', function (event) {
                $(this).find('ul.variable-items-wrapper').each(function () {

                    let selected = '',
                        options  = $(this).siblings('select.woo-variation-raw-select').find('option'),
                        current  = $(this).siblings('select.woo-variation-raw-select').find('option:selected'),
                        eq       = $(this).siblings('select.woo-variation-raw-select').find('option').eq(1),
                        li       = $(this).find('li'),
                        selects  = [];

                    // For Avada FIX
                    if (options.length < 1) {
                        options = $(this).parent().find('select.woo-variation-raw-select').find('option');
                        current = $(this).parent().find('select.woo-variation-raw-select').find('option:selected');
                        eq      = $(this).parent().find('select.woo-variation-raw-select').find('option').eq(1);
                    }

                    options.each(function () {
                        if ($(this).val() !== '') {
                            selects.push($(this).val());
                            selected = current ? current.val() : eq.val();
                        }
                    });

                    _.delay(() => {
                        li.each(function () {
                            let value = $(this).attr('data-value');
                            $(this).removeClass('selected disabled').addClass('disabled');

                            if (_.contains(selects, value)) {

                                $(this).removeClass('disabled');

                                $(this).find('input.wvs-radio-variable-item:radio').prop('disabled', false);

                                if (value === selected) {

                                    $(this).addClass('selected');

                                    if ($(this).hasClass('radio-variable-item')) {
                                        $(this).find('input.wvs-radio-variable-item:radio').prop('checked', true);
                                    }
                                }
                            }
                            else {

                                if ($(this).hasClass('radio-variable-item')) {
                                    $(this).find('input.wvs-radio-variable-item:radio').prop('disabled', true).prop('checked', false);
                                }
                            }
                        });

                        // Items Updated
                        $(this).trigger('wvs-items-updated');
                    }, 1);

                });
            });
        }

    }

    /**
     * ------------------------------------------------------------------------
     * jQuery
     * ------------------------------------------------------------------------
     */

    $.fn['WooVariationSwatches'] = WooVariationSwatches._jQueryInterface;
    $.fn['WooVariationSwatches'].Constructor = WooVariationSwatches;
    $.fn['WooVariationSwatches'].noConflict  = function () {
        $.fn['WooVariationSwatches'] = $.fn['WooVariationSwatches'];
        return WooVariationSwatches._jQueryInterface
    }

    return WooVariationSwatches;

})(jQuery);

export default WooVariationSwatches