(function($) {
    'use strict';

    wp.customize.controlConstructor['ast-color'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;
            var container = control.container;

            var picker = container.find('.ast-color-picker');
            var hidden = container.find('.ast-color-value');

            picker.wpColorPicker({
                change: function(event, ui) {
                    hidden.val(ui.color.toString()).trigger('change');
                    control.setting.set(ui.color.toString());
                },
                clear: function() {
                    hidden.val('').trigger('change');
                    control.setting.set('');
                }
            });

            container.find('.ast-color-swatch').on('click', function() {
                var color = $(this).data('color');
                picker.wpColorPicker('color', color);
                hidden.val(color).trigger('change');
                control.setting.set(color);
                container.find('.ast-color-swatch').removeClass('active');
                $(this).addClass('active');
            });
        }
    });
})(jQuery);
