<?php
/*
Plugin Name: TeeTime Booking Pro
Description: A complete tee time booking solution with real-time availability, customer management, and payments.
Version: 2.1
Author: Golf Payments
Text Domain: teetime-booking-pro
Requires Plugins: woocommerce 
*/

if (!defined('ABSPATH')) exit;

// Define constants
define('TTBP_PATH', plugin_dir_path(__FILE__));
define('TTBP_URL', plugin_dir_url(__FILE__));
define('TTBP_VERSION', '1.0.0');

// Database class
require_once TTBP_PATH . 'includes/class-ttbp-database.php';

// Load available functions
require_once TTBP_PATH . 'includes/functions.php';

register_activation_hook(__FILE__, 'ttbp_activate_plugin');

function ttbp_activate_plugin()
{
    TTBP_Database::create_tables();

    if (function_exists('TTBP__declare_defaults')) {
        TTBP__declare_defaults();
    } else {
        TTBP__add_notice('Defaults are not declared, contact support', 'error');
    }

    add_role('course_staff', 'Course Staff', [
        'read' => true,
        'course_staff' => true,
    ]);
}


// Autoload core classes
require_once TTBP_PATH . 'includes/class-teetime-booking-pro.php';

// Init plugin
function ttbp_init_plugin()
{
    $timezone = get_option('ttbp_default_timezone', 'UTC');
    date_default_timezone_set($timezone);
    $plugin = new TeeTime_Booking_Pro();
    $plugin->init();    
}
add_action('plugins_loaded', 'ttbp_init_plugin');
