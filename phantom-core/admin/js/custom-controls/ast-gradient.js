(function($) {
    'use strict';

    wp.customize.controlConstructor['ast-gradient'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;
            var container = control.container;
            var color1 = container.find('.ast-gradient-color-1');
            var color2 = container.find('.ast-gradient-color-2');
            var angle = container.find('.ast-gradient-angle');
            var angleLabel = container.find('.ast-gradient-angle-value');
            var preview = container.find('.ast-gradient-preview');
            var hidden = container.find('.ast-gradient-value');

            function update() {
                var c1 = color1.val();
                var c2 = color2.val();
                var a  = angle.val();
                var gradient = 'linear-gradient(' + a + 'deg, ' + c1 + ', ' + c2 + ')';
                preview.css('background', gradient);
                angleLabel.text(a + '°');
                hidden.val(JSON.stringify({ color1: c1, color2: c2, angle: a })).trigger('change');
                control.setting.set({ color1: c1, color2: c2, angle: a });
            }

            color1.wpColorPicker({ change: update, clear: update });
            color2.wpColorPicker({ change: update, clear: update });
            angle.on('input', update);
        }
    });
})(jQuery);
