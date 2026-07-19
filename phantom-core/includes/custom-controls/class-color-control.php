<?php
declare(strict_types=1);

namespace PhantomCore\Customizer\Controls;

defined( 'ABSPATH' ) || exit;

class Color_Control extends Control_Base {

    public static function get_type(): string {
        return 'ast-color';
    }

    public static function get_sanitize_callback(): callable {
        return function ( $value ) {
            if ( preg_match( '/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $value ) ) {
                return $value;
            }
            if ( preg_match( '/^rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*(?:0|1|0?\.\d+)\s*\)$/', $value ) ) {
                return $value;
            }
            return '#000000';
        };
    }

    public function enqueue(): void {
        wp_enqueue_script( 'phantom-ast-color', PHANTOM_CORE_URL . 'admin/js/custom-controls/ast-color.js', array( 'customize-controls', 'wp-color-picker' ), PHANTOM_CORE_VERSION, true );
        wp_enqueue_style( 'wp-color-picker' );
    }

    public function render_content(): void {
        $palette = array( '#000000', '#ffffff', '#2271b1', '#135e96', '#72aee6', '#f0f0f1', '#3c434a', '#2c3338', '#dcdcde' );
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php if ( $this->description ) : ?>
                <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
            <?php endif; ?>
        </label>
        <div class="ast-color-container">
            <input type="text" class="ast-color-picker" value="<?php echo esc_attr( $this->value() ); ?>"
                   data-alpha="true" data-palette="<?php echo esc_attr( wp_json_encode( $palette ) ); ?>" />
            <input type="hidden" class="ast-color-value" <?php $this->link(); ?> />
            <div class="ast-color-palette">
                <?php foreach ( $palette as $swatch ) : ?>
                    <span class="ast-color-swatch" data-color="<?php echo esc_attr( $swatch ); ?>"
                          style="background:<?php echo esc_attr( $swatch ); ?>"></span>
                <?php endforeach; ?>
            </div>
        </div>
        <style>
        .ast-color-container { padding:6px 0; }
        .ast-color-picker { width:100% !important; }
        .ast-color-palette { display:flex; gap:4px; margin-top:8px; flex-wrap:wrap; }
        .ast-color-swatch { width:24px; height:24px; border-radius:50%; cursor:pointer; border:2px solid transparent; transition:border-color 0.15s; }
        .ast-color-swatch:hover { border-color:#2271b1; }
        .ast-color-swatch.active { border-color:#135e96; }
        </style>
        <?php
    }
}
