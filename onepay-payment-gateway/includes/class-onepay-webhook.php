<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_Webhook {
    
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
    }
    
    public function register_webhook_endpoint() {
        register_rest_route('onepay/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_webhook'),
            'permission_callback' => '__return_true',
        ));
    }
    
    public function handle_webhook($request) {
        $body = $request->get_body();
        $data = json_decode($body, true);
        
        OnePay_Logger::log('Webhook received: ' . print_r($data, true));
        
        if (!$this->verify_webhook($request)) {
            OnePay_Logger::log('Webhook verification failed', 'error');
            return new WP_Error('verification_failed', 'Webhook verification failed', array('status' => 401));
        }
        
        if (!isset($data['type']) || !isset($data['data'])) {
            OnePay_Logger::log('Invalid webhook data', 'error');
            return new WP_Error('invalid_data', 'Invalid webhook data', array('status' => 400));
        }
        
        $event_type = $data['type'];
        $event_data = $data['data'];
        
        switch ($event_type) {
            case 'payment.succeeded':
                $this->handle_payment_succeeded($event_data);
                break;
            
            case 'payment.failed':
                $this->handle_payment_failed($event_data);
                break;
            
            case 'payment.canceled':
                $this->handle_payment_canceled($event_data);
                break;
            
            case 'refund.succeeded':
                $this->handle_refund_succeeded($event_data);
                break;
            
            case 'refund.failed':
                $this->handle_refund_failed($event_data);
                break;
            
            case 'subscription.created':
                $this->handle_subscription_created($event_data);
                break;
            
            case 'subscription.updated':
                $this->handle_subscription_updated($event_data);
                break;
            
            case 'subscription.canceled':
                $this->handle_subscription_canceled($event_data);
                break;
            
            case 'subscription.payment_succeeded':
                $this->handle_subscription_payment_succeeded($event_data);
                break;
            
            case 'subscription.payment_failed':
                $this->handle_subscription_payment_failed($event_data);
                break;
            
            default:
                OnePay_Logger::log('Unknown webhook event type: ' . $event_type);
                break;
        }
        
        return rest_ensure_response(array('received' => true));
    }
    
    private function verify_webhook($request) {
        $webhook_secret = get_option('onepay_webhook_secret', '');
        
        if (empty($webhook_secret)) {
            OnePay_Logger::log('Webhook secret not configured - rejecting webhook', 'error');
            return false;
        }
        
        $signature = $request->get_header('onepay-signature');
        
        if (empty($signature)) {
            OnePay_Logger::log('Webhook signature missing', 'error');
            return false;
        }
        
        $body = $request->get_body();
        $expected_signature = hash_hmac('sha256', $body, $webhook_secret);
        
        return hash_equals($expected_signature, $signature);
    }
    
    private function handle_payment_succeeded($data) {
        $payment_id = $data['id'];
        
        OnePay_DB::update_transaction_by_payment_id($payment_id, array(
            'status' => 'succeeded',
            'metadata' => $data,
        ));
        
        if (isset($data['metadata']['order_id'])) {
            $order_id = $data['metadata']['order_id'];
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->payment_complete($payment_id);
                $order->add_order_note(__('OnePay payment succeeded. Payment ID: ', 'onepay-payment-gateway') . $payment_id);
            }
        }
        
        do_action('onepay_payment_succeeded', $data);
    }
    
    private function handle_payment_failed($data) {
        $payment_id = $data['id'];
        
        OnePay_DB::update_transaction_by_payment_id($payment_id, array(
            'status' => 'failed',
            'metadata' => $data,
        ));
        
        if (isset($data['metadata']['order_id'])) {
            $order_id = $data['metadata']['order_id'];
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->update_status('failed', __('OnePay payment failed. ', 'onepay-payment-gateway'));
            }
        }
        
        do_action('onepay_payment_failed', $data);
    }
    
    private function handle_payment_canceled($data) {
        $payment_id = $data['id'];
        
        OnePay_DB::update_transaction_by_payment_id($payment_id, array(
            'status' => 'canceled',
            'metadata' => $data,
        ));
        
        if (isset($data['metadata']['order_id'])) {
            $order_id = $data['metadata']['order_id'];
            $order = wc_get_order($order_id);
            
            if ($order) {
                $order->update_status('cancelled', __('OnePay payment canceled. ', 'onepay-payment-gateway'));
            }
        }
        
        do_action('onepay_payment_canceled', $data);
    }
    
    private function handle_refund_succeeded($data) {
        $refund_id = $data['id'];
        $payment_id = $data['payment_id'];
        
        OnePay_Logger::log('Refund succeeded: ' . $refund_id . ' for payment: ' . $payment_id);
        
        do_action('onepay_refund_succeeded', $data);
    }
    
    private function handle_refund_failed($data) {
        OnePay_Logger::log('Refund failed: ' . print_r($data, true), 'error');
        
        do_action('onepay_refund_failed', $data);
    }
    
    private function handle_subscription_created($data) {
        $subscription_id = $data['id'];
        
        OnePay_DB::log_subscription(array(
            'subscription_id' => $subscription_id,
            'customer_email' => isset($data['customer']['email']) ? $data['customer']['email'] : '',
            'plan_id' => isset($data['plan_id']) ? $data['plan_id'] : '',
            'status' => 'active',
            'amount' => isset($data['amount']) ? $data['amount'] : 0,
            'currency' => isset($data['currency']) ? $data['currency'] : 'USD',
            'interval' => isset($data['interval']) ? $data['interval'] : '',
            'metadata' => $data,
        ));
        
        do_action('onepay_subscription_created', $data);
    }
    
    private function handle_subscription_updated($data) {
        $subscription_id = $data['id'];
        
        OnePay_DB::update_subscription($subscription_id, array(
            'status' => isset($data['status']) ? $data['status'] : 'active',
        ));
        
        do_action('onepay_subscription_updated', $data);
    }
    
    private function handle_subscription_canceled($data) {
        $subscription_id = $data['id'];
        
        OnePay_DB::update_subscription($subscription_id, array(
            'status' => 'canceled',
        ));
        
        do_action('onepay_subscription_canceled', $data);
    }
    
    private function handle_subscription_payment_succeeded($data) {
        OnePay_Logger::log('Subscription payment succeeded: ' . print_r($data, true));
        
        do_action('onepay_subscription_payment_succeeded', $data);
    }
    
    private function handle_subscription_payment_failed($data) {
        OnePay_Logger::log('Subscription payment failed: ' . print_r($data, true), 'error');
        
        do_action('onepay_subscription_payment_failed', $data);
    }
}

new OnePay_Webhook();
