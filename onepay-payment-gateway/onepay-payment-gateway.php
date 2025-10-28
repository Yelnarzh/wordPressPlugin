<?php
/**
 * Plugin Name: OnePay Payment Gateway
 * Plugin URI: https://onepayltd.kz
 * Description: Complete payment solution integrating OnePay API with WooCommerce. Supports one-time payments, subscriptions, refunds, and custom payment forms.
 * Version: 1.0.0
 * Author: Your Company Name
 * Author URI: https://yourcompany.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: onepay-payment-gateway
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ONEPAY_VERSION', '1.0.0');
define('ONEPAY_PLUGIN_FILE', __FILE__);
define('ONEPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ONEPAY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ONEPAY_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-main.php';

function onepay_init() {
    return OnePay_Main::instance();
}

add_action('plugins_loaded', 'onepay_init');

register_activation_hook(__FILE__, array('OnePay_Main', 'activate'));
register_deactivation_hook(__FILE__, array('OnePay_Main', 'deactivate'));
