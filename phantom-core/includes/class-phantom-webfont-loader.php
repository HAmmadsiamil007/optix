<?php
declare(strict_types=1);

defined('ABSPATH') || exit;

class Phantom_Webfont_Loader {
    private static ?Phantom_Webfont_Loader $instance = null;

    public static function instance(): Phantom_Webfont_Loader {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_local_fonts'), 11);
    }

    public function enqueue_local_fonts(): void {
        $upload_dir = wp_upload_dir();
        $font_dir = $upload_dir['basedir'] . '/phantom-fonts/';
        $font_url = $upload_dir['baseurl'] . '/phantom-fonts/';

        if (is_dir($font_dir)) {
            $files = scandir($font_dir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                    wp_enqueue_style(
                        'phantom-local-font-' . sanitize_title($file),
                        $font_url . $file,
                        array(),
                        PHANTOM_CORE_VERSION
                    );
                }
            }
        }
    }

    public static function get_download_dir(): string {
        $upload_dir = wp_upload_dir();
        return $upload_dir['basedir'] . '/phantom-fonts/';
    }
}
