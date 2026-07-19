<?php
/**
 * Phantom Core — Font Families
 *
 * System fonts + Google Fonts list + fallback stacks.
 *
 * @package Phantom_Core
 */

defined( 'ABSPATH' ) || exit;

class Phantom_Font_Families {

	private static ?Phantom_Font_Families $instance = null;

	public static function instance(): Phantom_Font_Families {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function get_system_fonts(): array {
		return array(
			'Arial'           => 'Arial, Helvetica, sans-serif',
			'Georgia'         => 'Georgia, "Times New Roman", serif',
			'Helvetica'       => 'Helvetica, Arial, sans-serif',
			'Tahoma'          => 'Tahoma, Geneva, sans-serif',
			'Times New Roman' => '"Times New Roman", Georgia, serif',
			'Trebuchet MS'    => '"Trebuchet MS", Helvetica, sans-serif',
			'Verdana'         => 'Verdana, Geneva, sans-serif',
		);
	}

	public function get_google_fonts(): array {
		return apply_filters( 'phantom_google_fonts', array(
			'Archivo'        => array( 100, 200, 300, 400, 500, 600, 700, 800, 900 ),
			'Playfair Display' => array( 400, 500, 600, 700, 800, 900 ),
			'Inter'          => array( 100, 200, 300, 400, 500, 600, 700, 800, 900 ),
			'Roboto'         => array( 100, 300, 400, 500, 700, 900 ),
			'Open Sans'      => array( 300, 400, 500, 600, 700, 800 ),
			'Lato'           => array( 100, 300, 400, 700, 900 ),
			'Montserrat'     => array( 100, 200, 300, 400, 500, 600, 700, 800, 900 ),
			'Poppins'        => array( 100, 200, 300, 400, 500, 600, 700, 800, 900 ),
			'Merriweather'   => array( 300, 400, 700, 900 ),
			'Source Sans Pro' => array( 200, 300, 400, 600, 700, 900 ),
			'Nunito'         => array( 200, 300, 400, 600, 700, 800, 900 ),
			'Raleway'        => array( 100, 200, 300, 400, 500, 600, 700, 800, 900 ),
			'DM Sans'        => array( 400, 500, 700 ),
			'Noto Sans'      => array( 100, 200, 300, 400, 500, 600, 700, 800, 900 ),
		) );
	}

	public function get_font_stack( string $font_family ): string {
		$system = $this->get_system_fonts();
		if ( isset( $system[ $font_family ] ) ) {
			return $system[ $font_family ];
		}
		return '"' . $font_family . '", sans-serif';
	}
}
