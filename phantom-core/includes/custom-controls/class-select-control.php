<?php
declare(strict_types=1);

namespace PhantomCore\Customizer\Controls;

defined( 'ABSPATH' ) || exit;

class Select_Control extends Control_Base {

    public static function get_type(): string {
        return 'ast-select';
    }

    public static function get_sanitize_callback(): callable {
        return 'sanitize_text_field';
    }

    public function render_content(): void {
        if ( empty( $this->choices ) ) {
            return;
        }
        ?>
        <label>
            <span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
            <?php if ( $this->description ) : ?>
                <span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
            <?php endif; ?>
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
