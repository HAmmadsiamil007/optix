<?php
declare(strict_types=1);

namespace PhantomCore\Customizer\Controls;

defined( 'ABSPATH' ) || exit;

class Font_Families {

    public static function get_system_fonts(): array {
        return array(
            'Arial'         => 'Arial, Helvetica, sans-serif',
            'Georgia'       => 'Georgia, serif',
            'Helvetica'     => 'Helvetica, Arial, sans-serif',
            'Tahoma'        => 'Tahoma, Geneva, sans-serif',
            'Times New Roman' => '"Times New Roman", Times, serif',
            'Trebuchet MS'  => '"Trebuchet MS", Helvetica, sans-serif',
            'Verdana'       => 'Verdana, Geneva, sans-serif',
            'Courier New'   => '"Courier New", Courier, monospace',
        );
    }

    public static function get_google_fonts(): array {
        return array(
            'Open Sans'      => array( 'weights' => array( '300', '400', '500', '600', '700', '800' ), 'category' => 'sans-serif' ),
            'Roboto'         => array( 'weights' => array( '100', '300', '400', '500', '700', '900' ), 'category' => 'sans-serif' ),
            'Lato'           => array( 'weights' => array( '100', '300', '400', '700', '900' ), 'category' => 'sans-serif' ),
            'Montserrat'     => array( 'weights' => array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Poppins'        => array( 'weights' => array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Playfair Display' => array( 'weights' => array( '400', '500', '600', '700', '800', '900' ), 'category' => 'serif' ),
            'Merriweather'   => array( 'weights' => array( '300', '400', '700', '900' ), 'category' => 'serif' ),
            'Nunito'         => array( 'weights' => array( '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Raleway'        => array( 'weights' => array( '100', '200', '300', '400', '500', '600', '700', '800', '900' ), 'category' => 'sans-serif' ),
            'Ubuntu'         => array( 'weights' => array( '300', '400', '500', '700' ), 'category' => 'sans-serif' ),
        );
    }

    public static function get_all(): array {
        return array(
            'system' => self::get_system_fonts(),
            'google' => self::get_google_fonts(),
        );
    }
}
