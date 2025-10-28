<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_Logger {
    
    private static $log_enabled;
    
    public static function log($message, $level = 'info') {
        self::$log_enabled = get_option('onepay_enable_logging', 'yes') === 'yes';
        
        if (!self::$log_enabled) {
            return;
        }
        
        $log_file = ONEPAY_PLUGIN_DIR . 'logs/onepay.log';
        
        if (!file_exists(dirname($log_file))) {
            wp_mkdir_p(dirname($log_file));
        }
        
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        error_log($log_entry, 3, $log_file);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OnePay] ' . $message);
        }
    }
    
    public static function clear_logs() {
        $log_file = ONEPAY_PLUGIN_DIR . 'logs/onepay.log';
        if (file_exists($log_file)) {
            unlink($log_file);
        }
    }
}
