# Phase 3: Custom Control Types — Implementation Plan

**Goal:** Build and register 8 custom Customizer control types (ast-color, ast-toggle, ast-radio-image, ast-responsive-slider, ast-responsive-spacing, ast-typography, ast-gradient, ast-select)

**Architecture:** Each control gets its own PHP class in `includes/custom-controls/`, own JS file in `admin/js/custom-controls/`, and is auto-registered via a base class. Customizer `add_control()` switch routes registry types to new classes.

**Tech Stack:** PHP 8.0+, WordPress Customizer API, vanilla JS (no jQuery dependency), CSS3

## Global Constraints
- All PHP classes use `PhantomCore\Customizer\Controls` namespace
- All PHP files follow PSR-4 autoload within plugin (required via `class-control-base.php`)
- JS files are vanilla ES5 (WP compatibility), no transpilation step
- CSS for controls is inline in the PHP class (enqueued via `print_template()` or `enqueue()`)
- All control types register via `$wp_customize->register_control_type()` in base class
- File naming: `class-{name}-control.php` for PHP, `ast-{name}.js` for JS

---

### Task 3.1: Control Base Class + Customizer Wiring

**Files:**
- Create: `includes/custom-controls/class-control-base.php`
- Modify: `includes/class-customizer.php` (add type routing + autoload)

**Interfaces:**
- Consumes: `Settings_Registry` entries with `type` field
- Produces: `Phantom_Control_Base` abstract class with `register_all()` + `get_control_instance()` + `get_sanitize_callback()`

**Sub-tasks:**

- [ ] **3.1a: Create base class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

abstract class Phantom_Control_Base extends \WP_Customize_Control {
    abstract public static function get_type(): string;
    abstract public static function get_sanitize_callback(): callable;

    public static function register_all(\WP_Customize_Manager $wp_customize): void {
        $controls = [
            Color_Control::class,
            Toggle_Control::class,
            Radio_Image_Control::class,
            Responsive_Slider_Control::class,
            Responsive_Spacing_Control::class,
            Typography_Control::class,
            Gradient_Control::class,
            Select_Control::class,
        ];
        foreach ( $controls as $class ) {
            $wp_customize->register_control_type( $class );
        }
    }
}
```

- [ ] **3.1b: Add autoloader for control classes in `phantom-core.php`**

Add before the main plugin class:
```php
spl_autoload_register( function ( $class ) {
    $prefix   = 'PhantomCore\\Customizer\\Controls\\';
    $base_dir = PHANTOM_CORE_PATH . 'includes/custom-controls/';
    $len      = strlen( $prefix );
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }
    $relative_class = substr( $class, $len );
    $file           = $base_dir . 'class-' . str_replace( '_', '-', strtolower( $relative_class ) ) . '.php';
    if ( file_exists( $file ) ) {
        require $file;
    }
} );
```

- [ ] **3.1c: Extend Customizer `add_control()` switch**

In `class-customizer.php` `add_control()`, add routing for new types BEFORE the fallback to WP built-in controls:

```php
private function add_control( $wp_customize, $key, $entry, $section ) {
    // Route custom control types
    $custom_controls = array(
        'ast-color'            => Controls\Color_Control::class,
        'ast-toggle'           => Controls\Toggle_Control::class,
        'ast-radio-image'      => Controls\Radio_Image_Control::class,
        'ast-responsive-slider' => Controls\Responsive_Slider_Control::class,
        'ast-responsive-spacing' => Controls\Responsive_Spacing_Control::class,
        'ast-typography'       => Controls\Typography_Control::class,
        'ast-gradient'         => Controls\Gradient_Control::class,
        'ast-select'           => Controls\Select_Control::class,
    );

    $type = $entry['type'] ?? 'string';
    if ( isset( $custom_controls[ $type ] ) ) {
        $class = $custom_controls[ $type ];
        $wp_customize->add_control( new $class(
            $wp_customize,
            'phantom_' . $key,
            array(
                'label'       => $entry['label'] ?? '',
                'description' => $entry['description'] ?? '',
                'section'     => $section,
                'settings'    => 'phantom_' . $key,
                'priority'    => $entry['priority'] ?? 10,
                'input_attrs' => $entry['input_attrs'] ?? array(),
            )
        ) );
        return;
    }

    // ... existing switch for color, bool, select, etc. ...

    // Also add sanitize callback routing
    $sanitize_map = array(
        'ast-color'            => array( $this, 'sanitize_alpha_color' ),
        'ast-toggle'           => 'rest_sanitize_boolean',
        'ast-radio-image'      => 'sanitize_text_field',
        'ast-responsive-slider' => array( $this, 'sanitize_responsive_slider' ),
        'ast-responsive-spacing' => array( $this, 'sanitize_responsive_spacing' ),
        'ast-typography'       => array( $this, 'sanitize_typography' ),
        'ast-gradient'         => array( $this, 'sanitize_gradient' ),
        'ast-select'           => 'sanitize_text_field',
    );
}
```

Add sanitize callbacks to the Customizer class:

```php
public function sanitize_alpha_color( $value ): string {
    if ( preg_match( '/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $value ) ) {
        return $value;
    }
    if ( preg_match( '/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(?:0|1|0?\.\d+)\s*\)$/', $value ) ) {
        return $value;
    }
    return '#000000';
}

public function sanitize_responsive_slider( $value ): array {
    if ( ! is_array( $value ) ) {
        return array( 'desktop' => 50, 'tablet' => 50, 'mobile' => 50 );
    }
    return array(
        'desktop' => isset( $value['desktop'] ) ? floatval( $value['desktop'] ) : 50,
        'tablet'  => isset( $value['tablet'] ) ? floatval( $value['tablet'] ) : 50,
        'mobile'  => isset( $value['mobile'] ) ? floatval( $value['mobile'] ) : 50,
    );
}

public function sanitize_responsive_spacing( $value ): array {
    if ( ! is_array( $value ) ) {
        return array( 'desktop' => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ) );
    }
    $default_dir = array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 );
    $result = array();
    foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
        $result[ $device ] = isset( $value[ $device ] ) && is_array( $value[ $device ] )
            ? array_merge( $default_dir, array_map( 'floatval', $value[ $device ] ) )
            : $default_dir;
    }
    return $result;
}

public function sanitize_typography( $value ): array {
    $default = array(
        'family'         => '',
        'weight'         => '400',
        'style'          => 'normal',
        'transform'      => 'none',
        'size'           => array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ),
        'line_height'    => '1.5',
        'letter_spacing' => '0',
    );
    if ( ! is_array( $value ) ) {
        return $default;
    }
    $value['family']         = sanitize_text_field( $value['family'] ?? '' );
    $value['weight']         = sanitize_text_field( $value['weight'] ?? '400' );
    $value['style']          = sanitize_text_field( $value['style'] ?? 'normal' );
    $value['transform']      = sanitize_text_field( $value['transform'] ?? 'none' );
    $value['line_height']    = sanitize_text_field( $value['line_height'] ?? '1.5' );
    $value['letter_spacing'] = sanitize_text_field( $value['letter_spacing'] ?? '0' );
    if ( isset( $value['size'] ) && is_array( $value['size'] ) ) {
        foreach ( array( 'desktop', 'tablet', 'mobile' ) as $d ) {
            $value['size'][ $d ] = isset( $value['size'][ $d ] ) ? floatval( $value['size'][ $d ] ) : 16;
        }
    } else {
        $value['size'] = array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 );
    }
    return $value;
}

public function sanitize_gradient( $value ): array {
    if ( ! is_array( $value ) ) {
        return array( 'color1' => '#000000', 'color2' => '#ffffff', 'angle' => 0 );
    }
    return array(
        'color1' => $this->sanitize_alpha_color( $value['color1'] ?? '#000000' ),
        'color2' => $this->sanitize_alpha_color( $value['color2'] ?? '#ffffff' ),
        'angle'  => floatval( $value['angle'] ?? 0 ),
    );
}
```

- [ ] **3.1d: Add autoload call + base registration in `class-customizer.php` `init()`**

```php
public function init(): void {
    // Load custom control base (which registers all controls)
    $base_path = PHANTOM_CORE_PATH . 'includes/custom-controls/class-control-base.php';
    if ( file_exists( $base_path ) ) {
        require_once $base_path;
        add_action( 'customize_register', array( Controls\Phantom_Control_Base::class, 'register_all' ), 1 );
    }
    // ... existing init code ...
}
```

---

### Task 3.2: Build ast-toggle Control

**Files:**
- Create: `includes/custom-controls/class-toggle-control.php`
- Create: `admin/js/custom-controls/ast-toggle.js`

- [ ] **3.2a: Create Toggle_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Toggle_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-toggle'; }
    public static function get_sanitize_callback(): callable {
        return 'rest_sanitize_boolean';
    }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-toggle.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-toggle', $js_url, array( 'customize-controls' ), '1.0.0', true );
    }

    public function render_content(): void {
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
            <div class="ast-toggle-container">
                <input type="checkbox" id="ast-toggle-<?php echo esc_attr( $this->id ); ?>"
                       class="ast-toggle-input" value="1"
                       <?php checked( '1', $this->value() ); ?>
                       <?php $this->link(); ?> />
                <label class="ast-toggle-label" for="ast-toggle-<?php echo esc_attr( $this->id ); ?>">
                    <span class="ast-toggle-switch"></span>
                </label>
                <span class="ast-toggle-status"><?php echo $this->value() ? 'ON' : 'OFF'; ?></span>
            </div>
        </label>
        <style>
        .ast-toggle-container { display: flex; align-items: center; gap: 10px; padding: 6px 0; }
        .ast-toggle-input { display: none; }
        .ast-toggle-label { cursor: pointer; width: 44px; height: 24px; background: #ccc; border-radius: 12px; position: relative; transition: background 0.2s; }
        .ast-toggle-input:checked + .ast-toggle-label { background: #2271b1; }
        .ast-toggle-switch { position: absolute; top: 2px; left: 2px; width: 20px; height: 20px; background: #fff; border-radius: 50%; transition: transform 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
        .ast-toggle-input:checked + .ast-toggle-label .ast-toggle-switch { transform: translateX(20px); }
        .ast-toggle-status { font-size: 11px; text-transform: uppercase; color: #666; letter-spacing: 1px; font-weight: 600; }
        .ast-toggle-input:checked ~ .ast-toggle-status { color: #2271b1; }
        </style>
        <?php
    }
}
```

- [ ] **3.2b: Create ast-toggle.js**

```javascript
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
```

---

### Task 3.3: Build ast-select Control

**Files:**
- Create: `includes/custom-controls/class-select-control.php`

- [ ] **3.3a: Create Select_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Select_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-select'; }
    public static function get_sanitize_callback(): callable { return 'sanitize_text_field'; }

    public function render_content(): void {
        if ( empty( $this->choices ) ) return;
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
            <select <?php $this->link(); ?>>
                <?php foreach ( $this->choices as $value => $label ) : ?>
                    <?php if ( is_array( $label ) ) : ?>
                        <optgroup label="<?php echo esc_attr( $value ); ?>">
                            <?php foreach ( $label as $opt_val => $opt_label ) : ?>
                                <option value="<?php echo esc_attr( $opt_val ); ?>" <?php selected( $this->value(), $opt_val ); ?>>
                                    <?php echo esc_html( $opt_label ); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php else : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $this->value(), $value ); ?>>
                            <?php echo esc_html( $label ); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </label>
        <?php
    }
}
```

- [ ] **3.3b: No JS needed** — native select works via `$this->link()`

---

### Task 3.4: Build ast-radio-image Control

**Files:**
- Create: `includes/custom-controls/class-radio-image-control.php`
- Create: `admin/js/custom-controls/ast-radio-image.js`

- [ ] **3.4a: Create Radio_Image_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Radio_Image_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-radio-image'; }
    public static function get_sanitize_callback(): callable { return 'sanitize_text_field'; }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-radio-image.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-radio-image', $js_url, array( 'customize-controls' ), '1.0.0', true );
    }

    public function render_content(): void {
        if ( empty( $this->choices ) ) return;
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
        <div class="ast-radio-image-container">
            <?php foreach ( $this->choices as $value => $image_url ) : ?>
                <label class="ast-radio-image-item <?php echo selected( $this->value(), $value, false ) ? 'active' : ''; ?>">
                    <input type="radio" name="<?php echo esc_attr( $this->id ); ?>"
                           value="<?php echo esc_attr( $value ); ?>"
                           class="ast-radio-image-input"
                           <?php $this->link(); ?>
                           <?php checked( $this->value(), $value ); ?> />
                    <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $value ); ?>" />
                </label>
            <?php endforeach; ?>
        </div>
        <style>
        .ast-radio-image-container { display: flex; flex-wrap: wrap; gap: 6px; margin: 6px 0; }
        .ast-radio-image-item { cursor: pointer; border: 3px solid #e0e0e0; border-radius: 4px; overflow: hidden; transition: border-color 0.2s; }
        .ast-radio-image-item.active { border-color: #2271b1; }
        .ast-radio-image-item img { display: block; width: 48px; height: 48px; object-fit: cover; margin: 0; }
        .ast-radio-image-input { display: none; }
        </style>
        <?php
    }
}
```

- [ ] **3.4b: Create ast-radio-image.js**

```javascript
(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-radio-image'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;
            this.container.on('click', '.ast-radio-image-item', function() {
                var input = $(this).find('.ast-radio-image-input');
                input.prop('checked', true);
                control.container.find('.ast-radio-image-item').removeClass('active');
                $(this).addClass('active');
                control.setting.set(input.val());
            });
        }
    });
})(jQuery);
```

---

### Task 3.5: Build ast-color (Alpha) Control

**Files:**
- Create: `includes/custom-controls/class-color-control.php`
- Create: `admin/js/custom-controls/ast-color.js`

- [ ] **3.5a: Create Color_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Color_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-color'; }
    public static function get_sanitize_callback(): callable {
        return function( $value ) {
            if ( preg_match( '/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $value ) ) return $value;
            if ( preg_match( '/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(?:0|1|0?\.\d+)\s*\)$/', $value ) ) return $value;
            return '#000000';
        };
    }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-color.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-color', $js_url, array( 'customize-controls', 'wp-color-picker' ), '1.0.0', true );
        wp_enqueue_style( 'wp-color-picker' );
    }

    public function render_content(): void {
        $palette = array( '#000000', '#ffffff', '#2271b1', '#135e96', '#72aee6', '#f0f0f1', '#3c434a', '#2c3338', '#dcdcde' );
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
        <div class="ast-color-container">
            <input type="text" class="ast-color-picker" value="<?php echo esc_attr( $this->value() ); ?>"
                   data-alpha="true" data-palette="<?php echo esc_attr( json_encode( $palette ) ); ?>" />
            <input type="hidden" class="ast-color-value" <?php $this->link(); ?> />
            <div class="ast-color-palette">
                <?php foreach ( $palette as $swatch ) : ?>
                    <span class="ast-color-swatch" data-color="<?php echo esc_attr( $swatch ); ?>"
                          style="background:<?php echo esc_attr( $swatch ); ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
        .ast-color-container { padding: 6px 0; }
        .ast-color-picker { width: 100% !important; }
        .ast-color-palette { display: flex; gap: 4px; margin-top: 8px; flex-wrap: wrap; }
        .ast-color-swatch { width: 24px; height: 24px; border-radius: 50%; cursor: pointer; border: 2px solid transparent; transition: border-color 0.15s; }
        .ast-color-swatch:hover { border-color: #2271b1; }
        .ast-color-swatch.active { border-color: #135e96; }
        </style>
        <?php
    }
}
```

- [ ] **3.5b: Create ast-color.js** (leverages Iris/Color Picker with alpha override)

```javascript
(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-color'] = wp.customize.Control.extend({
        ready: function() {
            var control = this,
                $input = this.container.find('.ast-color-picker'),
                $hidden = this.container.find('.ast-color-value');

            $input.wpColorPicker({
                change: function(event, ui) {
                    control.setting.set(ui.color.toString());
                },
                clear: function() {
                    control.setting.set('');
                },
                palettes: $input.data('palette') || true
            });

            // Sync palette swatch clicks
            this.container.on('click', '.ast-color-swatch', function() {
                var color = $(this).data('color');
                $input.iris('color', color).iris('hide');
                control.setting.set(color);
            });

            // Sync external changes back to picker
            this.setting.bind(function(val) {
                $input.iris('color', val);
            });
        }
    });
})(jQuery);
```

---

### Task 3.6: Build ast-gradient Control

**Files:**
- Create: `includes/custom-controls/class-gradient-control.php`
- Create: `admin/js/custom-controls/ast-gradient.js`

- [ ] **3.6a: Create Gradient_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Gradient_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-gradient'; }
    public static function get_sanitize_callback(): callable {
        return function( $value ) {
            if ( ! is_array( $value ) ) return array( 'color1' => '#000000', 'color2' => '#ffffff', 'angle' => 0 );
            $sanitize = function( $c ) {
                return preg_match( '/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $c ) ? $c : '#000000';
            };
            return array(
                'color1' => $sanitize( $value['color1'] ?? '#000000' ),
                'color2' => $sanitize( $value['color2'] ?? '#ffffff' ),
                'angle'  => floatval( $value['angle'] ?? 0 ),
            );
        };
    }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-gradient.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-gradient', $js_url, array( 'customize-controls', 'wp-color-picker' ), '1.0.0', true );
        wp_enqueue_style( 'wp-color-picker' );
    }

    public function render_content(): void {
        $val = $this->value();
        $val = is_array( $val ) ? $val : array( 'color1' => '#000000', 'color2' => '#ffffff', 'angle' => 0 );
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
        <div class="ast-gradient-container">
            <div class="ast-gradient-preview" style="background: linear-gradient(<?php echo esc_attr( $val['angle'] ); ?>deg, <?php echo esc_attr( $val['color1'] ); ?>, <?php echo esc_attr( $val['color2'] ); ?>); height: 40px; border-radius: 4px; margin-bottom: 8px;"></div>
            <div class="ast-gradient-colors">
                <label>Color 1: <input type="text" class="ast-gradient-color-1" value="<?php echo esc_attr( $val['color1'] ); ?>" data-alpha="true" /></label>
                <label>Color 2: <input type="text" class="ast-gradient-color-2" value="<?php echo esc_attr( $val['color2'] ); ?>" data-alpha="true" /></label>
            </div>
            <label>Angle: <input type="range" class="ast-gradient-angle" min="0" max="360" value="<?php echo esc_attr( $val['angle'] ); ?>" /> <span class="ast-gradient-angle-value"><?php echo esc_html( $val['angle'] ); ?>°</span></label>
            <input type="hidden" class="ast-gradient-value" <?php $this->link(); ?> />
        </div>
        <style>
        .ast-gradient-colors { display: flex; gap: 8px; margin: 6px 0; }
        .ast-gradient-colors label { flex: 1; font-size: 11px; color: #666; }
        .ast-gradient-colors input { width: 100%; }
        .ast-gradient-angle { width: 80%; }
        </style>
        <?php
    }
}
```

- [ ] **3.6b: Create ast-gradient.js**

```javascript
(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-gradient'] = wp.customize.Control.extend({
        ready: function() {
            var control = this,
                $preview = this.container.find('.ast-gradient-preview'),
                $hidden = this.container.find('.ast-gradient-value');

            function updateGradient() {
                var c1 = $('.ast-gradient-color-1', control.container).val(),
                    c2 = $('.ast-gradient-color-2', control.container).val(),
                    angle = $('.ast-gradient-angle', control.container).val(),
                    gradient = 'linear-gradient(' + angle + 'deg, ' + c1 + ', ' + c2 + ')';
                $preview.css('background', gradient);
                control.setting.set(JSON.stringify({ color1: c1, color2: c2, angle: parseFloat(angle) }));
            }

            this.container.on('change', '.ast-gradient-color-1, .ast-gradient-color-2, .ast-gradient-angle', updateGradient);
            this.container.on('input', '.ast-gradient-angle', function() {
                $('.ast-gradient-angle-value', control.container).text($(this).val() + '°');
                updateGradient();
            });

            $('.ast-gradient-color-1, .ast-gradient-color-2', this.container).wpColorPicker({
                change: function() { updateGradient(); }
            });
        }
    });
})(jQuery);
```

---

### Task 3.7: Build ast-responsive-slider Control

**Files:**
- Create: `includes/custom-controls/class-responsive-slider-control.php`
- Create: `admin/js/custom-controls/ast-responsive-slider.js`

- [ ] **3.7a: Create Responsive_Slider_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Responsive_Slider_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-responsive-slider'; }
    public static function get_sanitize_callback(): callable {
        return function( $value ) {
            if ( ! is_array( $value ) ) return array( 'desktop' => 50, 'tablet' => 50, 'mobile' => 50 );
            return array(
                'desktop' => isset( $value['desktop'] ) ? floatval( $value['desktop'] ) : 50,
                'tablet'  => isset( $value['tablet'] ) ? floatval( $value['tablet'] ) : 50,
                'mobile'  => isset( $value['mobile'] ) ? floatval( $value['mobile'] ) : 50,
            );
        };
    }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-responsive-slider.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-responsive-slider', $js_url, array( 'customize-controls' ), '1.0.0', true );
    }

    public function render_content(): void {
        $val = $this->value();
        $val = is_array( $val ) ? $val : array( 'desktop' => 50, 'tablet' => 50, 'mobile' => 50 );
        $min = $this->input_attrs['min'] ?? 0;
        $max = $this->input_attrs['max'] ?? 100;
        $step = $this->input_attrs['step'] ?? 1;
        $unit = $this->input_attrs['unit'] ?? 'px';
        $devices = array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' );
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
        <div class="ast-responsive-slider-container">
            <div class="ast-device-tabs">
                <?php foreach ( $devices as $key => $name ) : ?>
                    <button type="button" class="ast-device-tab <?php echo $key === 'desktop' ? 'active' : ''; ?>" data-device="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?></button>
                <?php endforeach; ?>
            </div>
            <?php foreach ( $devices as $key => $name ) : ?>
                <div class="ast-slider-device" data-device="<?php echo esc_attr( $key ); ?>" <?php echo $key !== 'desktop' ? 'style="display:none"' : ''; ?>>
                    <input type="range" class="ast-responsive-slider" data-device="<?php echo esc_attr( $key ); ?>"
                           min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>" step="<?php echo esc_attr( $step ); ?>"
                           value="<?php echo esc_attr( $val[ $key ] ); ?>" />
                    <input type="number" class="ast-responsive-slider-input" data-device="<?php echo esc_attr( $key ); ?>"
                           value="<?php echo esc_attr( $val[ $key ] ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
                           step="<?php echo esc_attr( $step ); ?>" />
                    <span class="ast-slider-unit"><?php echo esc_html( $unit ); ?></span>
                </div>
            <?php endforeach; ?>
            <input type="hidden" class="ast-responsive-slider-value" <?php $this->link(); ?> />
        </div>
        <style>
        .ast-device-tabs { display: flex; gap: 2px; margin-bottom: 8px; }
        .ast-device-tab { padding: 4px 12px; border: 1px solid #ddd; background: #f0f0f1; cursor: pointer; font-size: 11px; border-radius: 3px; }
        .ast-device-tab.active { background: #2271b1; color: #fff; border-color: #2271b1; }
        .ast-slider-device { display: flex; align-items: center; gap: 8px; margin: 4px 0; }
        .ast-responsive-slider { flex: 1; }
        .ast-responsive-slider-input { width: 60px; }
        .ast-slider-unit { font-size: 11px; color: #666; min-width: 20px; }
        </style>
        <?php
    }
}
```

- [ ] **3.7b: Create ast-responsive-slider.js**

```javascript
(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-responsive-slider'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;

            this.container.on('click', '.ast-device-tab', function() {
                var device = $(this).data('device');
                $(this).addClass('active').siblings().removeClass('active');
                $('.ast-slider-device', control.container).hide();
                $('.ast-slider-device[data-device="' + device + '"]', control.container).show();
            });

            function syncValue(container) {
                var value = {};
                $('.ast-responsive-slider', container).each(function() {
                    value[$(this).data('device')] = parseFloat($(this).val());
                });
                control.setting.set(JSON.stringify(value));
            }

            this.container.on('input change', '.ast-responsive-slider', function() {
                var device = $(this).data('device');
                $('.ast-responsive-slider-input[data-device="' + device + '"]', control.container).val($(this).val());
                syncValue(control.container);
            });

            this.container.on('input change', '.ast-responsive-slider-input', function() {
                var device = $(this).data('device');
                $('.ast-responsive-slider[data-device="' + device + '"]', control.container).val($(this).val());
                syncValue(control.container);
            });
        }
    });
})(jQuery);
```

---

### Task 3.8: Build ast-responsive-spacing Control

**Files:**
- Create: `includes/custom-controls/class-responsive-spacing-control.php`
- Create: `admin/js/custom-controls/ast-responsive-spacing.js`

- [ ] **3.8a: Create Responsive_Spacing_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Responsive_Spacing_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-responsive-spacing'; }
    public static function get_sanitize_callback(): callable {
        return function( $value ) {
            if ( ! is_array( $value ) ) return array( 'desktop' => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ) );
            $default = array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 );
            $result = array();
            foreach ( array( 'desktop', 'tablet', 'mobile' ) as $device ) {
                $result[ $device ] = isset( $value[ $device ] ) && is_array( $value[ $device ] )
                    ? array_merge( $default, array_map( 'floatval', $value[ $device ] ) )
                    : $default;
            }
            return $result;
        };
    }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-responsive-spacing.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-responsive-spacing', $js_url, array( 'customize-controls' ), '1.0.0', true );
    }

    public function render_content(): void {
        $val = $this->value();
        $val = is_array( $val ) ? $val : array( 'desktop' => array( 'top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0 ) );
        $directions = array( 'top' => 'Top', 'right' => 'Right', 'bottom' => 'Bottom', 'left' => 'Left' );
        $devices = array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' );
        $unit = $this->input_attrs['unit'] ?? 'px';
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
        <div class="ast-responsive-spacing-container">
            <div class="ast-device-tabs">
                <?php foreach ( $devices as $key => $name ) : ?>
                    <button type="button" class="ast-device-tab <?php echo $key === 'desktop' ? 'active' : ''; ?>" data-device="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?></button>
                <?php endforeach; ?>
                <button type="button" class="ast-spacing-linked" title="Link values">🔗</button>
            </div>
            <?php foreach ( $devices as $key => $name ) : ?>
                <div class="ast-spacing-device" data-device="<?php echo esc_attr( $key ); ?>" <?php echo $key !== 'desktop' ? 'style="display:none"' : ''; ?>>
                    <?php foreach ( $directions as $dir_key => $dir_name ) : ?>
                        <label class="ast-spacing-direction">
                            <span><?php echo esc_html( $dir_name ); ?></span>
                            <input type="number" class="ast-spacing-input" data-device="<?php echo esc_attr( $key ); ?>" data-direction="<?php echo esc_attr( $dir_key ); ?>"
                                   value="<?php echo esc_attr( $val[ $key ][ $dir_key ] ?? 0 ); ?>" min="0" step="1" />
                            <span class="ast-spacing-unit"><?php echo esc_html( $unit ); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <input type="hidden" class="ast-responsive-spacing-value" <?php $this->link(); ?> />
        </div>
        <style>
        .ast-spacing-device { display: flex; gap: 4px; margin: 6px 0; }
        .ast-spacing-direction { display: flex; flex-direction: column; align-items: center; flex: 1; }
        .ast-spacing-direction span { font-size: 9px; text-transform: uppercase; color: #888; }
        .ast-spacing-input { width: 100%; text-align: center; }
        .ast-spacing-unit { font-size: 10px; color: #666; }
        .ast-spacing-linked { margin-left: auto; cursor: pointer; padding: 2px 8px; font-size: 14px; }
        .ast-spacing-linked.linked { opacity: 0.5; }
        </style>
        <?php
    }
}
```

- [ ] **3.8b: Create ast-responsive-spacing.js**

```javascript
(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-responsive-spacing'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;

            this.container.on('click', '.ast-device-tab', function() {
                var device = $(this).data('device');
                $(this).addClass('active').siblings('.ast-device-tab').removeClass('active');
                $('.ast-spacing-device', control.container).hide();
                $('.ast-spacing-device[data-device="' + device + '"]', control.container).show();
            });

            function syncValue(container) {
                var value = {};
                $('.ast-spacing-device', container).each(function() {
                    var device = $(this).data('device');
                    value[device] = {};
                    $('.ast-spacing-input', $(this)).each(function() {
                        value[device][$(this).data('direction')] = parseFloat($(this).val()) || 0;
                    });
                });
                control.setting.set(JSON.stringify(value));
            }

            this.container.on('input change', '.ast-spacing-input', function() {
                var container = control.container;
                if (container.find('.ast-spacing-linked').hasClass('linked')) {
                    var val = $(this).val();
                    var device = $(this).data('device');
                    $('.ast-spacing-input[data-device="' + device + '"]', container).val(val);
                }
                syncValue(container);
            });

            this.container.on('click', '.ast-spacing-linked', function() {
                $(this).toggleClass('linked');
            });
        }
    });
})(jQuery);
```

---

### Task 3.9: Build ast-typography Control (Most Complex)

**Files:**
- Create: `includes/custom-controls/class-typography-control.php`
- Create: `admin/js/custom-controls/ast-typography.js`
- Create: `includes/class-phantom-font-families.php` (font data source)

- [ ] **3.9a: Create font families data class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Font_Families {
    public static function get_system_fonts(): array {
        return array(
            'Arial' => 'Arial, Helvetica, sans-serif',
            'Georgia' => 'Georgia, serif',
            'Helvetica' => 'Helvetica, Arial, sans-serif',
            'Tahoma' => 'Tahoma, Geneva, sans-serif',
            'Times New Roman' => '"Times New Roman", Times, serif',
            'Trebuchet MS' => '"Trebuchet MS", Helvetica, sans-serif',
            'Verdana' => 'Verdana, Geneva, sans-serif',
            'Courier New' => '"Courier New", Courier, monospace',
        );
    }

    public static function get_google_fonts(): array {
        return array(
            'Open Sans' => array( 'weights' => array( '300', '400', '500', '600', '700', '800' ), 'category' => 'sans-serif' ),
            'Roboto' => array( 'weights' => array( '100', '300', '400', '500', '700', '900' ), 'category' => 'sans-serif' ),
            'Lato' => array( 'weights' => array( '100', '300', '400', '700', '900' ), 'category' => 'sans-serif' ),
            'Montserrat' => array( 'weights' => array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Poppins' => array( 'weights' => array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Playfair Display' => array( 'weights' => array( '400', '500', '600', '700', '800', '900' ), 'category' => 'serif' ),
            'Merriweather' => array( 'weights' => array( '300', '400', '700', '900' ), 'category' => 'serif' ),
            'Nunito' => array( 'weights' => array( '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Raleway' => array( 'weights' => array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Ubuntu' => array( 'weights' => array( '300', '400', '500', '700' ), 'category' => 'sans-serif' ),
        );
    }

    public static function get_all(): array {
        return array( 'system' => self::get_system_fonts(), 'google' => self::get_google_fonts() );
    }
}
```

- [ ] **3.9b: Create Typography_Control class**

```php
<?php
namespace PhantomCore\Customizer\Controls;

class Typography_Control extends Phantom_Control_Base {
    public static function get_type(): string { return 'ast-typography'; }
    public static function get_sanitize_callback(): callable {
        return function( $value ) {
            $default = array(
                'family' => '', 'weight' => '400', 'style' => 'normal',
                'transform' => 'none', 'line_height' => '1.5', 'letter_spacing' => '0',
                'size' => array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ),
            );
            if ( ! is_array( $value ) ) return $default;
            $value['family'] = sanitize_text_field( $value['family'] ?? '' );
            $value['weight'] = sanitize_text_field( $value['weight'] ?? '400' );
            $value['style'] = sanitize_text_field( $value['style'] ?? 'normal' );
            $value['transform'] = sanitize_text_field( $value['transform'] ?? 'none' );
            $value['line_height'] = sanitize_text_field( $value['line_height'] ?? '1.5' );
            $value['letter_spacing'] = sanitize_text_field( $value['letter_spacing'] ?? '0' );
            $value['size'] = is_array( $value['size'] ?? null )
                ? array_merge( array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ), array_map( 'floatval', $value['size'] ) )
                : array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 );
            return $value;
        };
    }

    public function enqueue(): void {
        $js_url = plugins_url( 'admin/js/custom-controls/ast-typography.js', PHANTOM_CORE_FILE );
        wp_enqueue_script( 'phantom-ast-typography', $js_url, array( 'customize-controls' ), '1.0.0', true );
        wp_localize_script( 'phantom-ast-typography', 'PhantomFonts', Font_Families::get_all() );
    }

    public function render_content(): void {
        $val = $this->value();
        $val = is_array( $val ) ? $val : array( 'family' => '', 'weight' => '400', 'style' => 'normal', 'transform' => 'none', 'size' => array( 'desktop' => 16, 'tablet' => 16, 'mobile' => 16 ), 'line_height' => '1.5', 'letter_spacing' => '0' );
        $weights = array( '100', '200', '300', '400', '500', '600', '700', '800', '900' );
        $styles = array( 'normal' => 'Normal', 'italic' => 'Italic' );
        $transforms = array( 'none' => 'None', 'uppercase' => 'UPPERCASE', 'lowercase' => 'lowercase', 'capitalize' => 'Capitalize' );
        $devices = array( 'desktop' => 'Desktop', 'tablet' => 'Tablet', 'mobile' => 'Mobile' );
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
        </label>
        <div class="ast-typography-container">
            <div class="ast-typography-row">
                <select class="ast-typography-family" data-default="">
                    <option value="">System Fonts</option>
                    <optgroup label="System Fonts">
                        <?php foreach ( Font_Families::get_system_fonts() as $name => $stack ) : ?>
                            <option value="<?php echo esc_attr( $name ); ?>" <?php selected( $val['family'], $name ); ?>><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Google Fonts">
                        <?php foreach ( Font_Families::get_google_fonts() as $name => $data ) : ?>
                            <option value="<?php echo esc_attr( $name ); ?>" <?php selected( $val['family'], $name ); ?>><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="ast-typography-row" style="display:flex;gap:4px;">
                <select class="ast-typography-weight" style="flex:1;">
                    <?php foreach ( $weights as $w ) : ?>
                        <option value="<?php echo esc_attr( $w ); ?>" <?php selected( $val['weight'], $w ); ?>><?php echo esc_html( $w ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="ast-typography-style" style="flex:1;">
                    <?php foreach ( $styles as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $val['style'], $k ); ?>><?php echo esc_html( $v ); ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="ast-typography-transform" style="flex:1;">
                    <?php foreach ( $transforms as $k => $v ) : ?>
                        <option value="<?php echo esc_attr( $k ); ?>" <?php selected( $val['transform'], $k ); ?>><?php echo esc_html( $v ); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="ast-typography-row">
                <div class="ast-device-tabs">
                    <?php foreach ( $devices as $key => $name ) : ?>
                        <button type="button" class="ast-device-tab <?php echo $key === 'desktop' ? 'active' : ''; ?>" data-device="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $name ); ?></button>
                    <?php endforeach; ?>
                </div>
                <?php foreach ( $devices as $key => $name ) : ?>
                    <div class="ast-typography-size-device" data-device="<?php echo esc_attr( $key ); ?>" <?php echo $key !== 'desktop' ? 'style="display:none"' : ''; ?>>
                        <input type="number" class="ast-typography-size" data-device="<?php echo esc_attr( $key ); ?>"
                               value="<?php echo esc_attr( $val['size'][ $key ] ?? 16 ); ?>" min="0" step="1" /> px
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="ast-typography-row" style="display:flex;gap:8px;">
                <label style="flex:1;">Line Height <input type="text" class="ast-typography-line-height" value="<?php echo esc_attr( $val['line_height'] ); ?>" /></label>
                <label style="flex:1;">Letter Spacing <input type="text" class="ast-typography-letter-spacing" value="<?php echo esc_attr( $val['letter_spacing'] ); ?>" /> px</label>
            </div>
            <input type="hidden" class="ast-typography-value" <?php $this->link(); ?> />
        </div>
        <?php
    }
}
```

- [ ] **3.9c: Create ast-typography.js**

```javascript
(function($) {
    'use strict';
    wp.customize.controlConstructor['ast-typography'] = wp.customize.Control.extend({
        ready: function() {
            var control = this;

            this.container.on('click', '.ast-device-tab', function() {
                var device = $(this).data('device');
                $(this).addClass('active').siblings().removeClass('active');
                $('.ast-typography-size-device', control.container).hide();
                $('.ast-typography-size-device[data-device="' + device + '"]', control.container).show();
            });

            function syncValue() {
                var c = control.container;
                var value = {
                    family: $('.ast-typography-family', c).val(),
                    weight: $('.ast-typography-weight', c).val(),
                    style: $('.ast-typography-style', c).val(),
                    transform: $('.ast-typography-transform', c).val(),
                    line_height: $('.ast-typography-line-height', c).val(),
                    letter_spacing: $('.ast-typography-letter-spacing', c).val(),
                    size: {}
                };
                $('.ast-typography-size', c).each(function() {
                    value.size[$(this).data('device')] = parseFloat($(this).val()) || 16;
                });
                control.setting.set(JSON.stringify(value));
            }

            this.container.on('change input', '.ast-typography-family, .ast-typography-weight, .ast-typography-style, .ast-typography-transform, .ast-typography-size, .ast-typography-line-height, .ast-typography-letter-spacing', syncValue);
        }
    });
})(jQuery);
```

---

### Task 3.10: Integration — Upgrade Registry Settings to New Control Types

**Files:**
- Modify: `includes/class-settings-registry.php` (change `type` for 40+ settings)
- Modify: `includes/class-custom-css.php` (add responsive CSS generation)

- [ ] **3.10a: Upgrade color settings to `ast-color`**

In `class-settings-registry.php`, find all ~14 settings with `'type' => 'color'` that should use alpha. Change them to `'type' => 'ast-color'`.

Key settings to upgrade:
- `primary_color`, `secondary_color`, `heading_color`, `text_color`, `link_color`, `link_hover_color`
- `header_bg_color`, `footer_bg_color`, `hero_overlay_color`
- `button_bg_color`, `button_text_color`, `button_hover_bg_color`
- `body_bg_color`, `accent_color`

- [ ] **3.10b: Upgrade bool settings to `ast-toggle` (optional)**

Find settings where a toggle switch is more intuitive than checkbox. Change `'type' => 'bool'` to `'type' => 'ast-toggle'`.

Candidates: `display_header`, `display_footer`, `display_post_meta`, `enable_live_search`, `enable_quick_view`, `enable_image_zoom`, `enable_minicart`, `shop_catalog_mode`, `shop_wishlist_enable`, `shop_product_image_zoom`, `sticky_header` etc.

- [ ] **3.10c: Upgrade typography settings to `ast-typography`**

Find settings in the typography section that are currently individual `string`/`int`/`select` settings and replace with composite `ast-typography` type. Start with `body_font`, `heading_1_font`, `heading_2_font`, `heading_3_font`.

- [ ] **3.10d: Upgrade layout settings to `ast-responsive-slider`**

Find container/layout settings and add `'type' => 'ast-responsive-slider'` with responsive defaults:
- `container_width` → `{desktop: 1200, tablet: 960, mobile: 480}`
- `container_spacing` → `{desktop: 30, tablet: 20, mobile: 10}`
- `site_title_size`, `tagline_size` → responsive font sizes

- [ ] **3.10e: Upgrade spacing settings to `ast-responsive-spacing`**

Find padding/margin settings and add `'type' => 'ast-responsive-spacing'`:
- `header_padding`, `footer_padding`, `section_padding`
- `button_padding` → `{desktop: {top:12, right:24, bottom:12, left:24}, tablet: ..., mobile: ...}`

- [ ] **3.10f: Add `input_attrs` to responsive slider settings**

For each responsive slider, add appropriate `input_attrs`:
```php
'input_attrs' => array(
    'min'  => 0,
    'max'  => 2000,
    'step' => 1,
    'unit' => 'px',
),
```

---

### Task 3.11: Verification

- [ ] **3.11a: PHP syntax check**

Run: `docker exec phantom_wordpress php -l /var/www/html/wp-content/plugins/phantom-core/includes/custom-controls/class-{name}-control.php` for all 8 + base

- [ ] **3.11b: JS parse check**

Run: `node -e "require('fs').readdirSync('admin/js/custom-controls/').filter(f=>f.endsWith('.js')).forEach(f=>{try{new Function(require('fs').readFileSync('admin/js/custom-controls/'+f,'utf8'));console.log(f+': OK')}catch(e){console.log(f+': FAIL')}})"`

- [ ] **3.11c: Customizer panel render test**

Open `http://localhost:8080/wp-admin/customize.php` and verify no JS errors.

- [ ] **3.11d: REST API health check**

Verify all endpoints still respond 200 after changes.

---
