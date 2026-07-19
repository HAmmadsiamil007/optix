wp.customize.controlConstructor['ast-select'] = wp.customize.Control.extend({
    ready: function() {
        var control = this;
        this.container.on('change', 'select', function() {
            control.setting.set(this.value);
        });
    }
});
