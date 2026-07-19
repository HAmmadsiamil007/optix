<?php
/**
 * Partial render callbacks for Selective Refresh.
 *
 * Each function returns an HTML fragment for the given setting key.
 *
 * @package Phantom_Core
 */

defined( 'ABSPATH' ) || exit;

function phantom_render_header_partial(): void {
	get_template_part( 'template-parts/header/layout' );
}

function phantom_render_footer_partial(): void {
	get_template_part( 'template-parts/footer/layout' );
}

function phantom_render_blog_partial(): void {
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			get_template_part( 'template-parts/blog/content', get_post_format() );
		}
	} else {
		get_template_part( 'template-parts/blog/content', 'none' );
	}
}

function phantom_render_search_partial(): void {
	get_template_part( 'template-parts/search/results' );
}
