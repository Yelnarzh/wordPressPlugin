<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_API {
    
    private $api_key;
    private $api_secret;
    private $api_url;
    private $test_mode;
    
    public function __construct() {
        $this->api_key = get_option('onepay_api_key', '');
        $this->api_secret = get_option('onepay_api_secret', '');
        $this->test_mode = get_option('onepay_test_mode', 'yes') === 'yes';
        $this->api_url = $this->test_mode 
            ? 'https://sandbox.onepayltd.kz/api' 
            : 'https://api.onepayltd.kz/api';
    }
    
    private function get_headers() {
        return array(
            'Content-Type' => 'application/json',
            'api-key' => $this->api_key,
        );
    }
    
    public function create_payment($data) {
        $endpoint = '/payments';
        
        $payment_data = array(
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'customer' => array(
                'email' => $data['customer_email'],
                'name' => $data['customer_name'],
            ),
            'description' => $data['description'],
            'return_url' => $data['return_url'],
            'metadata' => isset($data['metadata']) ? $data['metadata'] : array(),
        );
        
        if (isset($data['payment_method'])) {
            $payment_data['payment_method'] = $data['payment_method'];
        }
        
        return $this->make_request('POST', $endpoint, $payment_data);
    }
    
    public function capture_payment($payment_id, $amount = null) {
        $endpoint = '/payments/' . $payment_id . '/capture';
        
        $data = array();
        if ($amount !== null) {
            $data['amount_to_capture'] = $amount;
        }
        
        return $this->make_request('POST', $endpoint, $data);
    }
    
    public function create_subscription($data) {
        $endpoint = '/subscriptions';
        
        $subscription_data = array(
            'customer' => array(
                'email' => $data['customer_email'],
                'name' => $data['customer_name'],
            ),
            'plan_id' => $data['plan_id'],
            'payment_method' => $data['payment_method'],
            'metadata' => isset($data['metadata']) ? $data['metadata'] : array(),
        );
        
        return $this->make_request('POST', $endpoint, $subscription_data);
    }
    
    public function cancel_subscription($subscription_id) {
        $endpoint = '/subscriptions/' . $subscription_id . '/cancel';
        return $this->make_request('POST', $endpoint, array());
    }
    
    public function create_refund($payment_id, $amount = null, $reason = '') {
        $endpoint = '/refunds';
        
        $refund_data = array(
            'payment_id' => $payment_id,
            'reason' => $reason,
        );
        
        if ($amount !== null) {
            $refund_data['amount'] = $amount;
        }
        
        return $this->make_request('POST', $endpoint, $refund_data);
    }
    
    public function get_payment($payment_id) {
        $endpoint = '/payments/' . $payment_id;
        return $this->make_request('GET', $endpoint);
    }
    
    public function get_refund($refund_id) {
        $endpoint = '/refunds/' . $refund_id;
        return $this->make_request('GET', $endpoint);
    }
    
    public function create_customer($data) {
        $endpoint = '/customers';
        
        $customer_data = array(
            'email' => $data['email'],
            'name' => $data['name'],
            'phone' => isset($data['phone']) ? $data['phone'] : '',
            'metadata' => isset($data['metadata']) ? $data['metadata'] : array(),
        );
        
        return $this->make_request('POST', $endpoint, $customer_data);
    }
    
    public function create_payment_method($data) {
        $endpoint = '/payment_methods';
        
        $payment_method_data = array(
            'type' => $data['type'],
            'card' => isset($data['card']) ? $data['card'] : array(),
            'billing_details' => isset($data['billing_details']) ? $data['billing_details'] : array(),
        );
        
        return $this->make_request('POST', $endpoint, $payment_method_data);
    }
    
    private function make_request($method, $endpoint, $data = array()) {
        $url = $this->api_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'headers' => $this->get_headers(),
            'timeout' => 30,
        );
        
        if (!empty($data) && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = json_encode($data);
        }
        
        OnePay_Logger::log('API Request: ' . $method . ' ' . $url);
        OnePay_Logger::log('Request Data: ' . print_r($data, true));
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            OnePay_Logger::log('API Error: ' . $response->get_error_message(), 'error');
            return array(
                'success' => false,
                'error' => $response->get_error_message(),
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);
        
        OnePay_Logger::log('API Response Code: ' . $response_code);
        OnePay_Logger::log('API Response: ' . print_r($response_data, true));
        
        if ($response_code >= 200 && $response_code < 300) {
            return array(
                'success' => true,
                'data' => $response_data,
            );
        } else {
            return array(
                'success' => false,
                'error' => isset($response_data['error']) ? $response_data['error'] : 'Unknown error',
                'code' => $response_code,
                'data' => $response_data,
            );
        }
    }
}
