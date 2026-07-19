(function ($) {
  'use strict';

  wp.customize.controlConstructor['ast-color-group'] = wp.customize.Control.extend({
    ready: function () {
      var control = this;
      var container = control.container.find('.ast-color-group-items');
      var groupId = container.data('group-id');

      if (control.params.input_attrs && control.params.input_attrs.children) {
        var children = control.params.input_attrs.children;
        children.forEach(function (child) {
          var row = $('<div class="ast-color-group-item"></div>');
          var label = $('<label class="ast-color-group-label">' + child.label + '</label>');
          var input = $('<input type="text" class="ast-color-picker" value="' + (child.value || '') + '" />');
          row.append(label).append(input);
          container.append(row);

          input.wpColorPicker({
            change: function () {
              control.setting.set(input.val());
            },
            clear: function () {
              control.setting.set('');
            }
          });
        });
      }
    }
  });

})(jQuery);
