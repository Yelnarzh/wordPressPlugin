<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_Subscriptions {
    
    private $api;
    
    public function __construct() {
        $this->api = new OnePay_API();
        
        add_action('onepay_check_subscriptions', array($this, 'check_subscription_renewals'));
        
        if (!wp_next_scheduled('onepay_check_subscriptions')) {
            wp_schedule_event(time(), 'daily', 'onepay_check_subscriptions');
        }
    }
    
    public function create_subscription($customer_email, $customer_name, $plan_id, $payment_method) {
        $data = array(
            'customer_email' => $customer_email,
            'customer_name' => $customer_name,
            'plan_id' => $plan_id,
            'payment_method' => $payment_method,
            'metadata' => array(
                'source' => 'wordpress',
                'site_url' => get_site_url(),
            ),
        );
        
        $result = $this->api->create_subscription($data);
        
        if ($result['success']) {
            $subscription_data = $result['data'];
            
            OnePay_DB::log_subscription(array(
                'subscription_id' => $subscription_data['id'],
                'customer_email' => $customer_email,
                'plan_id' => $plan_id,
                'status' => 'active',
                'amount' => isset($subscription_data['amount']) ? $subscription_data['amount'] : 0,
                'currency' => isset($subscription_data['currency']) ? $subscription_data['currency'] : 'USD',
                'interval' => isset($subscription_data['interval']) ? $subscription_data['interval'] : '',
                'next_payment_date' => isset($subscription_data['next_payment_date']) ? $subscription_data['next_payment_date'] : null,
                'metadata' => $subscription_data,
            ));
            
            OnePay_Logger::log('Subscription created: ' . $subscription_data['id']);
            
            return array(
                'success' => true,
                'subscription_id' => $subscription_data['id'],
                'data' => $subscription_data,
            );
        } else {
            OnePay_Logger::log('Subscription creation failed: ' . $result['error'], 'error');
            
            return array(
                'success' => false,
                'error' => $result['error'],
            );
        }
    }
    
    public function cancel_subscription($subscription_id) {
        $result = $this->api->cancel_subscription($subscription_id);
        
        if ($result['success']) {
            OnePay_DB::update_subscription($subscription_id, array(
                'status' => 'canceled',
            ));
            
            OnePay_Logger::log('Subscription canceled: ' . $subscription_id);
            
            return array('success' => true);
        } else {
            OnePay_Logger::log('Subscription cancellation failed: ' . $result['error'], 'error');
            
            return array(
                'success' => false,
                'error' => $result['error'],
            );
        }
    }
    
    public function check_subscription_renewals() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_subscriptions';
        
        $subscriptions = $wpdb->get_results(
            "SELECT * FROM $table WHERE status = 'active' AND next_payment_date <= NOW()",
            ARRAY_A
        );
        
        foreach ($subscriptions as $subscription) {
            OnePay_Logger::log('Processing subscription renewal: ' . $subscription['subscription_id']);
            
            do_action('onepay_subscription_renewal_due', $subscription);
        }
    }
}

new OnePay_Subscriptions();
