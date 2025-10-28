<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_Shortcodes {
    
    private $api;
    
    public function __construct() {
        $this->api = new OnePay_API();
        
        add_shortcode('onepay_payment_form', array($this, 'payment_form_shortcode'));
        add_shortcode('onepay_subscription_form', array($this, 'subscription_form_shortcode'));
        
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_onepay_process_payment', array($this, 'ajax_process_payment'));
        add_action('wp_ajax_nopriv_onepay_process_payment', array($this, 'ajax_process_payment'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('onepay-public-css', ONEPAY_PLUGIN_URL . 'public/css/public.css', array(), ONEPAY_VERSION);
        wp_enqueue_script('onepay-public-js', ONEPAY_PLUGIN_URL . 'public/js/public.js', array('jquery'), ONEPAY_VERSION, true);
        
        wp_localize_script('onepay-public-js', 'onepay_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('onepay_payment_nonce'),
        ));
    }
    
    public function payment_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'amount' => '',
            'currency' => 'USD',
            'description' => '',
            'button_text' => __('Pay Now', 'onepay-payment-gateway'),
        ), $atts);
        
        ob_start();
        ?>
        <div class="onepay-payment-form">
            <div class="onepay-security-notice">
                <p><strong><?php _e('Note:', 'onepay-payment-gateway'); ?></strong> <?php _e('This form creates a payment request and redirects you to OnePay\'s secure hosted payment page for card entry. Your card details are never stored on this website.', 'onepay-payment-gateway'); ?></p>
            </div>
            
            <form id="onepay-form" method="post">
                <?php if (empty($atts['amount'])) : ?>
                    <div class="form-group">
                        <label for="onepay-amount"><?php _e('Amount', 'onepay-payment-gateway'); ?></label>
                        <input type="number" id="onepay-amount" name="amount" step="0.01" min="0.01" required>
                    </div>
                <?php else : ?>
                    <input type="hidden" name="amount" value="<?php echo esc_attr($atts['amount']); ?>">
                <?php endif; ?>
                
                <input type="hidden" name="currency" value="<?php echo esc_attr($atts['currency']); ?>">
                <input type="hidden" name="description" value="<?php echo esc_attr($atts['description']); ?>">
                
                <div class="form-group">
                    <label for="onepay-name"><?php _e('Name', 'onepay-payment-gateway'); ?></label>
                    <input type="text" id="onepay-name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="onepay-email"><?php _e('Email', 'onepay-payment-gateway'); ?></label>
                    <input type="email" id="onepay-email" name="email" required>
                </div>
                
                <div class="onepay-errors"></div>
                
                <button type="submit" class="onepay-submit-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function subscription_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'plan_id' => '',
            'amount' => '',
            'interval' => 'monthly',
            'button_text' => __('Subscribe', 'onepay-payment-gateway'),
        ), $atts);
        
        if (empty($atts['plan_id'])) {
            return '<p>' . __('Error: Plan ID is required.', 'onepay-payment-gateway') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="onepay-subscription-form">
            <div class="onepay-security-notice">
                <p><strong><?php _e('Note:', 'onepay-payment-gateway'); ?></strong> <?php _e('This form creates a subscription and redirects you to OnePay\'s secure hosted page for card entry. Your card details are never stored on this website.', 'onepay-payment-gateway'); ?></p>
            </div>
            
            <form id="onepay-subscription-form" method="post">
                <input type="hidden" name="plan_id" value="<?php echo esc_attr($atts['plan_id']); ?>">
                <input type="hidden" name="action_type" value="subscription">
                
                <div class="subscription-details">
                    <p><strong><?php _e('Subscription Details:', 'onepay-payment-gateway'); ?></strong></p>
                    <?php if (!empty($atts['amount'])) : ?>
                        <p><?php echo sprintf(__('Amount: %s', 'onepay-payment-gateway'), esc_html($atts['amount'])); ?></p>
                    <?php endif; ?>
                    <p><?php echo sprintf(__('Billing: %s', 'onepay-payment-gateway'), esc_html($atts['interval'])); ?></p>
                </div>
                
                <div class="form-group">
                    <label for="onepay-sub-name"><?php _e('Name', 'onepay-payment-gateway'); ?></label>
                    <input type="text" id="onepay-sub-name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="onepay-sub-email"><?php _e('Email', 'onepay-payment-gateway'); ?></label>
                    <input type="email" id="onepay-sub-email" name="email" required>
                </div>
                
                <div class="onepay-errors"></div>
                
                <button type="submit" class="onepay-submit-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function ajax_process_payment() {
        check_ajax_referer('onepay_payment_nonce', 'nonce');
        
        $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
        $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : 'USD';
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $description = isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '';
        
        if ($amount <= 0 || empty($name) || empty($email)) {
            wp_send_json_error(array('message' => __('Invalid payment data.', 'onepay-payment-gateway')));
            return;
        }
        
        $payment_data = array(
            'amount' => $amount * 100,
            'currency' => $currency,
            'customer_email' => $email,
            'customer_name' => $name,
            'description' => $description,
            'return_url' => home_url(),
            'metadata' => array(
                'source' => 'shortcode',
                'site_url' => get_site_url(),
            ),
        );
        
        $result = $this->api->create_payment($payment_data);
        
        if ($result['success']) {
            $payment = $result['data'];
            
            OnePay_DB::log_transaction(array(
                'transaction_id' => 'sc_' . $payment['id'],
                'payment_id' => $payment['id'],
                'customer_email' => $email,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'type' => 'payment',
                'metadata' => $payment,
            ));
            
            wp_send_json_success(array(
                'payment_id' => $payment['id'],
                'redirect_url' => isset($payment['next_action']['redirect_to_url']) ? $payment['next_action']['redirect_to_url'] : '',
            ));
        } else {
            wp_send_json_error(array('message' => $result['error']));
        }
    }
}

new OnePay_Shortcodes();
