/*global wp*/
const WVS_Customize_Alpha_Color_Control = {
    ready() {

        let control  = this,
            updating = false,
            picker;

        picker = this.container.find('.wvs-color-picker');
        picker.val(control.setting()).wpColorPicker({
            change : function () {
                updating = true;
                control.setting.set(picker.wpColorPicker('color'));
                updating = false;
            },
            clear  : function () {
                updating = true;
                control.setting.set('');
                updating = false;
            }
        });

        control.setting.bind(function (value) {
            // Bail if the update came from the control itself.
            if (updating) {
                return;
            }
            picker.val(value);
            picker.wpColorPicker('color', value);
        });

        // Collapse color picker when hitting Esc instead of collapsing the current section.
        control.container.on('keydown', function (event) {
            let pickerContainer;
            if (27 !== event.which) { // Esc.
                return;
            }
            pickerContainer = control.container.find('.wp-picker-container');
            if (pickerContainer.hasClass('wp-picker-active')) {
                picker.wpColorPicker('close');
                control.container.find('.wp-color-result').focus();
                event.stopPropagation(); // Prevent section from being collapsed.
            }
        });
    }
};

wp.customize.controlConstructor['wvs-alpha-color'] = wp.customize.Control.extend(WVS_Customize_Alpha_Color_Control);