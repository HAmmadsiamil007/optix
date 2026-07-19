(function ($) {
  'use strict';

  wp.customize.controlConstructor['ast-background'] = wp.customize.Control.extend({
    ready: function () {
      var control = this;
      var fields = control.container.find('.ast-background-fields');
      var bgId = fields.data('bg-id');

      function collectValue() {
        return {
          color: fields.find('.ast-bg-color').val(),
          image: fields.find('.ast-bg-image').val(),
          position: fields.find('.ast-bg-position').val(),
          repeat: fields.find('.ast-bg-repeat').val(),
          size: fields.find('.ast-bg-size').val(),
          attachment: fields.find('.ast-bg-attachment').val(),
          overlay_color: fields.find('.ast-bg-overlay-color').val(),
          overlay_opacity: parseFloat(fields.find('.ast-bg-overlay-opacity').val()) || 0.5
        };
      }

      fields.find('.ast-bg-color').wpColorPicker({
        change: function () { control.setting.set(collectValue()); }
      });
    }
  });

})(jQuery);
