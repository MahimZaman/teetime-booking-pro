<?php

if (!defined('ABSPATH')) {
    exit;
}

class TeeTime_Booking_Pro
{
    private $shortcodes;
    private $ajax;
    private $cpt;
    private $admin;
    private $woo_modifier;

    public function init()
    {
        $this->load_dependencies();
        $this->init_classes();
        $this->register_hooks();
    }

    private function load_dependencies()
    {
        require_once TTBP_PATH . 'includes/class-ttbp-shortcodes.php';
        require_once TTBP_PATH . 'includes/class-ttbp-cpt.php';
        require_once TTBP_PATH . 'includes/class-ttbp-ajax.php';
        require_once TTBP_PATH . 'includes/class-ttbp-admin.php';
        require_once TTBP_PATH . 'includes/class-ttbp-woocommerce.php';
    }

    private function init_classes()
    {
        $this->shortcodes = new TTBP_Shortcodes();
        $this->cpt        = new TTBP_CPT();
        $this->admin      = new TTBP_Admin();
        $this->ajax  = new TTBP_Ajax();
        $this->woo_modifier = new TTBP_WooCommerce();
    }

    private function register_hooks()
    {
        // Enqueue scripts & styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

        // Register shortcodes
        add_action('init', [$this->shortcodes, 'register_shortcodes']);

        // Register CPT
        add_action('init', [$this->cpt, 'register_post_type']);
        add_action('init', [$this->cpt, 'register_taxonomy']);
    }

    public function enqueue_assets()
    {
        wp_enqueue_style('ttbp-flatpickr', 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css', [], time());
        wp_enqueue_style('ttbp-fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css', array(), time());
        wp_enqueue_style('ttbp-style', TTBP_URL . 'css/style.css', [], time());
        wp_enqueue_script('jquery');

        $sandbox = get_option('ttbp_clover_env') == 'test' ? true : false;
        $sdk = $sandbox ?  'https://checkout.sandbox.dev.clover.com/sdk.js' : 'https://checkout.clover.com/sdk.js';
        wp_enqueue_script('ttbp-clover-sdk', $sdk, ['jquery'], time(), true);
        // Flatpickr
        wp_enqueue_script('ttbp-flatpickr', 'https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js', time(), true);

        wp_enqueue_script('ttbp-calendar', TTBP_URL . 'js/calendar.js', ['jquery'], time(), true);
        wp_enqueue_script('ttbp-clover-pay', TTBP_URL . 'js/clover-pay.js', ['jquery'], time(), true);
        wp_enqueue_script('ttbp-booking', TTBP_URL . 'js/booking.js', ['jquery'], time(), true);
        wp_localize_script('ttbp-clover-pay', 'ttbp_clover', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'public_key' => get_option('ttbp_clover_public_key'),
            'merchantId' => get_option('ttbp_clover_merchant_id'),
            'nonce'    => wp_create_nonce('ttbp_nonce')
        ]);

        wp_localize_script('ttbp-booking', 'ttbp_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'booking_window' => get_option('ttbp_max_booking_window'),
            'nonce'    => wp_create_nonce('ttbp_nonce')
        ]);

        wp_localize_script('ttbp-calendar', 'ttbp_cal', [
            'booking_window' => get_option('ttbp_max_booking_window'),
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('ttbp_nonce'),
            'currency'           => get_option('ttbp_currency'),
            'currency_position'  => get_option('ttbp_currency_position'),
            'decimal_separator'  => get_option('ttbp_decimal_separator'),
            'thousand_separator' => get_option('ttbp_thousand_separator'),
            'decimals'           => get_option('ttbp_decimals'),
            'notice'           => get_option('ttbp_payment_notice'),
            'timezone'          => get_option('ttbp_default_timezone')
        ]);
    }
}
