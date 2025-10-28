<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_WC_Gateway extends WC_Payment_Gateway {
    
    private $api;
    
    public function __construct() {
        $this->id = 'onepay';
        $this->icon = '';
        $this->has_fields = false;
        $this->method_title = __('OnePay', 'onepay-payment-gateway');
        $this->method_description = __('Accept payments using OnePay payment gateway.', 'onepay-payment-gateway');
        
        $this->supports = array(
            'products',
            'refunds',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
        );
        
        $this->init_form_fields();
        $this->init_settings();
        
        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        
        $this->api = new OnePay_API();
        
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_onepay_return', array($this, 'handle_return'));
    }
    
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'onepay-payment-gateway'),
                'type' => 'checkbox',
                'label' => __('Enable OnePay payment gateway', 'onepay-payment-gateway'),
                'default' => 'no',
            ),
            'title' => array(
                'title' => __('Title', 'onepay-payment-gateway'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'onepay-payment-gateway'),
                'default' => __('Credit Card / Debit Card', 'onepay-payment-gateway'),
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __('Description', 'onepay-payment-gateway'),
                'type' => 'textarea',
                'description' => __('Payment method description that the customer will see on your checkout.', 'onepay-payment-gateway'),
                'default' => __('Pay securely with your credit or debit card via OnePay.', 'onepay-payment-gateway'),
                'desc_tip' => true,
            ),
        );
    }
    
    public function process_payment($order_id) {
        $order = wc_get_order($order_id);
        
        $transaction_id = 'wp_' . $order_id . '_' . time();
        
        $payment_data = array(
            'amount' => $order->get_total() * 100,
            'currency' => $order->get_currency(),
            'customer_email' => $order->get_billing_email(),
            'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'description' => sprintf(__('Order #%s', 'onepay-payment-gateway'), $order->get_order_number()),
            'return_url' => WC()->api_request_url('onepay_return'),
            'metadata' => array(
                'order_id' => $order_id,
                'customer_id' => $order->get_customer_id(),
                'site_url' => get_site_url(),
            ),
        );
        
        $result = $this->api->create_payment($payment_data);
        
        if ($result['success']) {
            $payment = $result['data'];
            
            OnePay_DB::log_transaction(array(
                'transaction_id' => $transaction_id,
                'order_id' => $order_id,
                'payment_id' => $payment['id'],
                'customer_email' => $order->get_billing_email(),
                'amount' => $order->get_total(),
                'currency' => $order->get_currency(),
                'status' => 'pending',
                'type' => 'payment',
                'metadata' => $payment,
            ));
            
            $order->update_meta_data('_onepay_payment_id', $payment['id']);
            $order->update_meta_data('_onepay_transaction_id', $transaction_id);
            $order->save();
            
            if (isset($payment['next_action']) && isset($payment['next_action']['redirect_to_url'])) {
                return array(
                    'result' => 'success',
                    'redirect' => $payment['next_action']['redirect_to_url'],
                );
            } elseif (isset($payment['status']) && $payment['status'] === 'succeeded') {
                $order->payment_complete($payment['id']);
                
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order),
                );
            } else {
                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true),
                );
            }
        } else {
            wc_add_notice(__('Payment error: ', 'onepay-payment-gateway') . $result['error'], 'error');
            
            OnePay_Logger::log('Payment failed for order ' . $order_id . ': ' . $result['error'], 'error');
            
            return array(
                'result' => 'fail',
                'redirect' => '',
            );
        }
    }
    
    public function process_refund($order_id, $amount = null, $reason = '') {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return new WP_Error('invalid_order', __('Invalid order ID.', 'onepay-payment-gateway'));
        }
        
        $payment_id = $order->get_meta('_onepay_payment_id');
        
        if (empty($payment_id)) {
            return new WP_Error('no_payment_id', __('No payment ID found for this order.', 'onepay-payment-gateway'));
        }
        
        $refund_amount = $amount ? $amount * 100 : null;
        
        $result = $this->api->create_refund($payment_id, $refund_amount, $reason);
        
        if ($result['success']) {
            $refund = $result['data'];
            
            $order->add_order_note(
                sprintf(
                    __('Refund processed via OnePay. Refund ID: %s. Amount: %s %s', 'onepay-payment-gateway'),
                    $refund['id'],
                    $amount,
                    $order->get_currency()
                )
            );
            
            OnePay_DB::log_transaction(array(
                'transaction_id' => 'refund_' . $refund['id'],
                'order_id' => $order_id,
                'payment_id' => $payment_id,
                'customer_email' => $order->get_billing_email(),
                'amount' => $amount,
                'currency' => $order->get_currency(),
                'status' => 'succeeded',
                'type' => 'refund',
                'metadata' => $refund,
            ));
            
            OnePay_Logger::log('Refund processed for order ' . $order_id . ': ' . $refund['id']);
            
            return true;
        } else {
            OnePay_Logger::log('Refund failed for order ' . $order_id . ': ' . $result['error'], 'error');
            
            return new WP_Error('refund_failed', $result['error']);
        }
    }
    
    public function handle_return() {
        global $woocommerce;
        
        if (isset($_GET['payment_id'])) {
            $payment_id = sanitize_text_field($_GET['payment_id']);
            
            $result = $this->api->get_payment($payment_id);
            
            if ($result['success']) {
                $payment = $result['data'];
                
                if (isset($payment['metadata']['order_id'])) {
                    $order_id = $payment['metadata']['order_id'];
                    $order = wc_get_order($order_id);
                    
                    if ($order && $payment['status'] === 'succeeded') {
                        $order->payment_complete($payment_id);
                        
                        wp_redirect($this->get_return_url($order));
                        exit;
                    }
                }
            }
        }
        
        wp_redirect(wc_get_page_permalink('cart'));
        exit;
    }
}
