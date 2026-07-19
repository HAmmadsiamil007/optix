<?php
/**
 * Phantom Core — CSS Generation Engine
 *
 * Filter-based modular CSS output, responsive CSS helpers,
 * and breakpoint management.
 *
 * @package Phantom_Core
 */

defined( 'ABSPATH' ) || exit;

class Phantom_Custom_CSS {

	const SITE_OPTION_PREFIX = 'phantom_';
	const CACHE_KEY = 'phantom_dynamic_css';
	const CACHE_TTL = 3600;

	private static ?Phantom_Custom_CSS $instance = null;

	public static function instance(): Phantom_Custom_CSS {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_css(): string {
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached ) {
			return $cached;
		}
		$css = '';
		$css = apply_filters( 'phantom_dynamic_css', $css );
		set_transient( self::CACHE_KEY, $css, self::CACHE_TTL );
		return $css;
	}

	public static function flush_cache(): void {
		delete_transient( self::CACHE_KEY );
	}

	public function render_style(): string {
		$css = $this->get_css();
		if ( empty( $css ) ) {
			return '';
		}
		return '<style id="phantom-inline-css">' . "\n" . $css . "\n" . '</style>';
	}

	public static function get_breakpoints(): array {
		$defaults = array(
			'tablet' => 768,
			'mobile' => 544,
		);
		return apply_filters( 'phantom_breakpoints', $defaults );
	}

	public static function responsive_css( string $setting_key, string $property, string $selector, string $unit = 'px' ): string {
		$breakpoints = self::get_breakpoints();
		$prefix      = self::SITE_OPTION_PREFIX;
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

	public static function minify_css( string $css ): string {
		$css = preg_replace( '#/\*.*?\*/#s', '', $css );
		$css = preg_replace( '/\s*\{\s*/', '{', $css );
		$css = preg_replace( '/\s*\}\s*/', '}', $css );
		$css = preg_replace( '/\s*:\s*/', ':', $css );
		$css = preg_replace( '/\s*;\s*/', ';', $css );
		$css = preg_replace( '/\s*,\s*/', ',', $css );
		$css = preg_replace( '/\s*>\s*/', '>', $css );
		$css = preg_replace( '/\s+/', ' ', $css );
		return trim( $css );
	}
}
