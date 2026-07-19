<?php
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'phantom_responsive_css' ) ) {
    function phantom_responsive_css( string $setting_key, string $property, string $selector, string $unit = 'px' ): string {
        $breakpoints = apply_filters( 'phantom_breakpoints', array( 'tablet' => 768, 'mobile' => 544 ) );
        $prefix      = 'phantom_';
        $value       = get_option( $prefix . $setting_key );
        $output      = '';

        if ( is_array( $value ) ) {
            $desktop = $value['desktop'] ?? '';
            $tablet  = $value['tablet'] ?? '';
            $mobile  = $value['mobile'] ?? '';

            if ( '' !== $desktop ) {
                $output .= "\t" . $selector . ' { ' . $property . ': ' . $desktop . $unit . '; }' . "\n";
            }
            if ( '' !== $tablet ) {
                $output .= '@media (max-width: ' . $breakpoints['tablet'] . 'px) {' . "\n";
                $output .= "\t" . $selector . ' { ' . $property . ': ' . $tablet . $unit . '; }' . "\n";
                $output .= '}' . "\n";
            }
            if ( '' !== $mobile ) {
                $output .= '@media (max-width: ' . $breakpoints['mobile'] . 'px) {' . "\n";
                $output .= "\t" . $selector . ' { ' . $property . ': ' . $mobile . $unit . '; }' . "\n";
                $output .= '}' . "\n";
            }
        } else {
            $scalar = $value;
            if ( '' !== $scalar ) {
                $output .= "\t" . $selector . ' { ' . $property . ': ' . $scalar . $unit . '; }' . "\n";
            }
        }

        return $output;
    }
}
