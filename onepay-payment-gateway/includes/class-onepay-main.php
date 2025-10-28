<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_Main {
    
    protected static $instance = null;
    
    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }
    
    private function includes() {
        require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-api.php';
        require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-db.php';
        require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-logger.php';
        require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-webhook.php';
        require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-subscriptions.php';
        require_once ONEPAY_PLUGIN_DIR . 'admin/class-onepay-admin.php';
        require_once ONEPAY_PLUGIN_DIR . 'public/class-onepay-shortcodes.php';
        
        if (class_exists('WooCommerce')) {
            require_once ONEPAY_PLUGIN_DIR . 'includes/class-onepay-wc-gateway.php';
        }
    }
    
    private function init_hooks() {
        add_action('init', array($this, 'load_textdomain'));
        add_filter('plugin_action_links_' . ONEPAY_PLUGIN_BASENAME, array($this, 'plugin_action_links'));
        
        if (class_exists('WooCommerce')) {
            add_filter('woocommerce_payment_gateways', array($this, 'add_gateway'));
        }
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('onepay-payment-gateway', false, dirname(ONEPAY_PLUGIN_BASENAME) . '/languages');
    }
    
    public function plugin_action_links($links) {
        $settings_link = '<a href="' . admin_url('admin.php?page=onepay-settings') . '">' . __('Settings', 'onepay-payment-gateway') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }
    
    public function add_gateway($gateways) {
        $gateways[] = 'OnePay_WC_Gateway';
        return $gateways;
    }
    
    public static function activate() {
        OnePay_DB::create_tables();
        flush_rewrite_rules();
    }
    
    public static function deactivate() {
        flush_rewrite_rules();
    }
}
