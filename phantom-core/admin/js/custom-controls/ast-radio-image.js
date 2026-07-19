(function($) {
    'use strict';

    wp.customize.controlConstructor['ast-radio-image'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;
            control.container.on('click', '.ast-radio-image-item', function() {
                var input = $(this).find('.ast-radio-image-input');
                input.prop('checked', true);
                control.container.find('.ast-radio-image-item').removeClass('active');
                $(this).addClass('active');
                control.setting.set(input.val());
            });
        }
    });
})(jQuery);
