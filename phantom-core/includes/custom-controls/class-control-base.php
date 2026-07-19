<?php
declare(strict_types=1);

namespace PhantomCore\Customizer\Controls;

use WP_Customize_Manager;

defined( 'ABSPATH' ) || exit;

abstract class Control_Base extends \WP_Customize_Control {

    abstract public static function get_type(): string;

    abstract public static function get_sanitize_callback(): callable;

    public static function register_all( WP_Customize_Manager $wp_customize ): void {
        $base = __DIR__ . '/';
        $controls = array(
            'class-font-families.php',
            'class-toggle-control.php',
            'class-select-control.php',
            'class-radio-image-control.php',
            'class-color-control.php',
            'class-gradient-control.php',
            'class-responsive-slider-control.php',
            'class-responsive-spacing-control.php',
            'class-typography-control.php',
            'class-color-group-control.php',
            'class-background-control.php',
            'class-border-control.php',
        );
        foreach ( $controls as $file ) {
            $path = $base . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
            }
        }
        $classes = array(
            Toggle_Control::class,
            Select_Control::class,
            Radio_Image_Control::class,
            Color_Control::class,
            Gradient_Control::class,
            Responsive_Slider_Control::class,
            Responsive_Spacing_Control::class,
            Typography_Control::class,
            Color_Group_Control::class,
            Background_Control::class,
            Border_Control::class,
        );
        foreach ( $classes as $class ) {
            if ( class_exists( $class ) ) {
                $wp_customize->register_control_type( $class );
            }
        }
    }

    private static array $type_class_map = array(
        'ast-color'             => Color_Control::class,
        'ast-toggle'            => Toggle_Control::class,
        'ast-radio-image'       => Radio_Image_Control::class,
        'ast-responsive-slider' => Responsive_Slider_Control::class,
        'ast-responsive-spacing'=> Responsive_Spacing_Control::class,
        'ast-typography'        => Typography_Control::class,
        'ast-gradient'          => Gradient_Control::class,
        'ast-select'            => Select_Control::class,
        'ast-color-group'       => Color_Group_Control::class,
        'ast-background'        => Background_Control::class,
        'ast-border'            => Border_Control::class,
    );

    public static function get_class_for_type( string $type ): ?string {
        return self::$type_class_map[ $type ] ?? null;
    }

    public static function get_sanitize_for_type( string $type ): ?callable {
        $class = self::get_class_for_type( $type );
        if ( $class && class_exists( $class ) && is_subclass_of( $class, self::class ) ) {
            return $class::get_sanitize_callback();
        }
        return null;
    }
}
