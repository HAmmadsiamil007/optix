<?php
/**
 * Colors / Buttons / Forms CSS Module
 *
 * @package Phantom_Core
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'phantom_dynamic_css',
	function ( string $css ): string {
		$keys = array(
			'primary_color', 'secondary_color', 'accent_color',
			'background_color', 'text_color', 'heading_color',
			'link_color', 'link_hover_color', 'border_color',
			'header_bg', 'header_text_color',
			'footer_bg', 'footer_text_color',
			'topbar_bg', 'topbar_text_color',
			'button_bg', 'button_text_color', 'button_hover_bg', 'button_hover_text_color',
			'input_bg', 'input_text_color', 'input_border_color', 'input_focus_border_color',
			'article_bg', 'article_text_color', 'widget_bg',
			'woo_primary', 'woo_secondary', 'woo_button_bg', 'woo_button_text_color',
			'woo_rating_color', 'woo_sale_badge_bg', 'woo_sale_badge_text',
			'submenu_bg', 'submenu_text_color',
		);

		$map = \PhantomCore\Settings_Registry::get_css_var_map();
		$output = '';

		foreach ( $keys as $k ) {
			if ( ! isset( $map[ $k ] ) ) {
				continue;
			}
			$val = get_option( 'phantom_' . $k, '' );
			if ( '' !== $val ) {
				$output .= "\t" . $map[ $k ] . ': ' . esc_attr( $val ) . ';' . "\n";
			}
		}

		if ( '' !== $output ) {
			$css .= ':root {' . "\n" . $output . '}' . "\n";
		}

		return $css;
	},
	10
);
