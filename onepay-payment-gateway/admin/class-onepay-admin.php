<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('OnePay Settings', 'onepay-payment-gateway'),
            __('OnePay', 'onepay-payment-gateway'),
            'manage_options',
            'onepay-settings',
            array($this, 'settings_page'),
            'dashicons-money-alt',
            56
        );
        
        add_submenu_page(
            'onepay-settings',
            __('Transactions', 'onepay-payment-gateway'),
            __('Transactions', 'onepay-payment-gateway'),
            'manage_options',
            'onepay-transactions',
            array($this, 'transactions_page')
        );
        
        add_submenu_page(
            'onepay-settings',
            __('Subscriptions', 'onepay-payment-gateway'),
            __('Subscriptions', 'onepay-payment-gateway'),
            'manage_options',
            'onepay-subscriptions',
            array($this, 'subscriptions_page')
        );
    }
    
    public function register_settings() {
        register_setting('onepay_settings', 'onepay_api_key');
        register_setting('onepay_settings', 'onepay_api_secret');
        register_setting('onepay_settings', 'onepay_test_mode');
        register_setting('onepay_settings', 'onepay_webhook_secret');
        register_setting('onepay_settings', 'onepay_enable_logging');
    }
    
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'onepay') === false) {
            return;
        }
        
        wp_enqueue_style('onepay-admin-css', ONEPAY_PLUGIN_URL . 'admin/css/admin.css', array(), ONEPAY_VERSION);
        wp_enqueue_script('onepay-admin-js', ONEPAY_PLUGIN_URL . 'admin/js/admin.js', array('jquery'), ONEPAY_VERSION, true);
    }
    
    public function settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('onepay_settings');
                do_settings_sections('onepay_settings');
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="onepay_test_mode"><?php _e('Test Mode', 'onepay-payment-gateway'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="onepay_test_mode" id="onepay_test_mode" value="yes" <?php checked(get_option('onepay_test_mode', 'yes'), 'yes'); ?>>
                            <p class="description"><?php _e('Enable test mode to use sandbox API.', 'onepay-payment-gateway'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="onepay_api_key"><?php _e('API Key', 'onepay-payment-gateway'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="onepay_api_key" id="onepay_api_key" value="<?php echo esc_attr(get_option('onepay_api_key', '')); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter your OnePay API key.', 'onepay-payment-gateway'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="onepay_api_secret"><?php _e('API Secret', 'onepay-payment-gateway'); ?></label>
                        </th>
                        <td>
                            <input type="password" name="onepay_api_secret" id="onepay_api_secret" value="<?php echo esc_attr(get_option('onepay_api_secret', '')); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter your OnePay API secret.', 'onepay-payment-gateway'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="onepay_webhook_secret"><?php _e('Webhook Secret', 'onepay-payment-gateway'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="onepay_webhook_secret" id="onepay_webhook_secret" value="<?php echo esc_attr(get_option('onepay_webhook_secret', '')); ?>" class="regular-text">
                            <p class="description"><?php _e('Enter your webhook secret for signature verification.', 'onepay-payment-gateway'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label><?php _e('Webhook URL', 'onepay-payment-gateway'); ?></label>
                        </th>
                        <td>
                            <code><?php echo esc_url(rest_url('onepay/v1/webhook')); ?></code>
                            <p class="description"><?php _e('Configure this URL in your OnePay dashboard to receive webhooks.', 'onepay-payment-gateway'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="onepay_enable_logging"><?php _e('Enable Logging', 'onepay-payment-gateway'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="onepay_enable_logging" id="onepay_enable_logging" value="yes" <?php checked(get_option('onepay_enable_logging', 'yes'), 'yes'); ?>>
                            <p class="description"><?php _e('Enable logging for debugging purposes.', 'onepay-payment-gateway'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function transactions_page() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_transactions';
        $transactions = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100", ARRAY_A);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Transactions', 'onepay-payment-gateway'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Transaction ID', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Order ID', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Payment ID', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Amount', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Status', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Type', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Date', 'onepay-payment-gateway'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)) : ?>
                        <tr>
                            <td colspan="7"><?php _e('No transactions found.', 'onepay-payment-gateway'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($transactions as $transaction) : ?>
                            <tr>
                                <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                                <td><?php echo esc_html($transaction['order_id']); ?></td>
                                <td><?php echo esc_html($transaction['payment_id']); ?></td>
                                <td><?php echo esc_html($transaction['amount'] . ' ' . $transaction['currency']); ?></td>
                                <td><span class="status-<?php echo esc_attr($transaction['status']); ?>"><?php echo esc_html($transaction['status']); ?></span></td>
                                <td><?php echo esc_html($transaction['type']); ?></td>
                                <td><?php echo esc_html($transaction['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
    
    public function subscriptions_page() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_subscriptions';
        $subscriptions = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 100", ARRAY_A);
        
        ?>
        <div class="wrap">
            <h1><?php _e('Subscriptions', 'onepay-payment-gateway'); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Subscription ID', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Customer Email', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Plan ID', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Amount', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Status', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Next Payment', 'onepay-payment-gateway'); ?></th>
                        <th><?php _e('Actions', 'onepay-payment-gateway'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($subscriptions)) : ?>
                        <tr>
                            <td colspan="7"><?php _e('No subscriptions found.', 'onepay-payment-gateway'); ?></td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($subscriptions as $subscription) : ?>
                            <tr>
                                <td><?php echo esc_html($subscription['subscription_id']); ?></td>
                                <td><?php echo esc_html($subscription['customer_email']); ?></td>
                                <td><?php echo esc_html($subscription['plan_id']); ?></td>
                                <td><?php echo esc_html($subscription['amount'] . ' ' . $subscription['currency']); ?></td>
                                <td><span class="status-<?php echo esc_attr($subscription['status']); ?>"><?php echo esc_html($subscription['status']); ?></span></td>
                                <td><?php echo esc_html($subscription['next_payment_date']); ?></td>
                                <td>
                                    <?php if ($subscription['status'] === 'active') : ?>
                                        <a href="#" class="button cancel-subscription" data-subscription-id="<?php echo esc_attr($subscription['subscription_id']); ?>"><?php _e('Cancel', 'onepay-payment-gateway'); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

new OnePay_Admin();
