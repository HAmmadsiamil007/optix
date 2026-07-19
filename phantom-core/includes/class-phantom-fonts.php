<?php
declare(strict_types=1);

defined('ABSPATH') || exit;

class Phantom_Fonts {
    private static ?Phantom_Fonts $instance = null;
    private array $fonts = array();

    public static function instance(): Phantom_Fonts {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_font(string $name, array $variants = array('400')): void {
        $this->fonts[$name] = $variants;
    }

    public function get_fonts(): array {
        return apply_filters('phantom_get_fonts', $this->fonts);
    }

    public function get_enqueue_url(): string {
        $fonts = $this->get_fonts();
        if (empty($fonts)) {
            return '';
        }
        $families = array();
        foreach ($fonts as $name => $variants) {
            $families[] = rawurlencode($name) . ':' . implode(',', $variants);
        }
        return 'https://fonts.googleapis.com/css2?family=' . implode('&family=', $families) . '&display=swap';
    }
}
