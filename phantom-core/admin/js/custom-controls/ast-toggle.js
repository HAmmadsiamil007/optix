(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-toggle'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;
            this.container.on('change', '.ast-toggle-input', function() {
                control.setting.set(this.checked ? '1' : '');
            });
        }
    });
})(jQuery);
