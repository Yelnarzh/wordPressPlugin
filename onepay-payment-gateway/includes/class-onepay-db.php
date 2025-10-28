<?php

if (!defined('ABSPATH')) {
    exit;
}

class OnePay_DB {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $transactions_table = $wpdb->prefix . 'onepay_transactions';
        $subscriptions_table = $wpdb->prefix . 'onepay_subscriptions';
        
        $sql = array();
        
        $sql[] = "CREATE TABLE IF NOT EXISTS $transactions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            transaction_id varchar(255) NOT NULL,
            order_id bigint(20) DEFAULT NULL,
            payment_id varchar(255) DEFAULT NULL,
            customer_email varchar(255) DEFAULT NULL,
            amount decimal(10,2) NOT NULL,
            currency varchar(10) NOT NULL,
            status varchar(50) NOT NULL,
            type varchar(50) NOT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY transaction_id (transaction_id),
            KEY payment_id (payment_id),
            KEY order_id (order_id),
            KEY status (status)
        ) $charset_collate;";
        
        $sql[] = "CREATE TABLE IF NOT EXISTS $subscriptions_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            subscription_id varchar(255) NOT NULL,
            customer_id bigint(20) DEFAULT NULL,
            customer_email varchar(255) DEFAULT NULL,
            plan_id varchar(255) DEFAULT NULL,
            status varchar(50) NOT NULL,
            amount decimal(10,2) NOT NULL,
            currency varchar(10) NOT NULL,
            interval varchar(50) DEFAULT NULL,
            next_payment_date datetime DEFAULT NULL,
            metadata longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY subscription_id (subscription_id),
            KEY customer_id (customer_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        foreach ($sql as $query) {
            dbDelta($query);
        }
        
        update_option('onepay_db_version', ONEPAY_VERSION);
    }
    
    public static function log_transaction($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_transactions';
        
        $wpdb->insert(
            $table,
            array(
                'transaction_id' => $data['transaction_id'],
                'order_id' => isset($data['order_id']) ? $data['order_id'] : null,
                'payment_id' => isset($data['payment_id']) ? $data['payment_id'] : null,
                'customer_email' => isset($data['customer_email']) ? $data['customer_email'] : null,
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'status' => $data['status'],
                'type' => $data['type'],
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%s', '%d', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function update_transaction($transaction_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_transactions';
        
        $update_data = array(
            'updated_at' => current_time('mysql'),
        );
        
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
        }
        
        if (isset($data['payment_id'])) {
            $update_data['payment_id'] = $data['payment_id'];
        }
        
        if (isset($data['metadata'])) {
            $update_data['metadata'] = json_encode($data['metadata']);
        }
        
        return $wpdb->update(
            $table,
            $update_data,
            array('transaction_id' => $transaction_id),
            array('%s', '%s', '%s', '%s'),
            array('%s')
        );
    }
    
    public static function get_transaction($transaction_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_transactions';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE transaction_id = %s", $transaction_id),
            ARRAY_A
        );
    }
    
    public static function log_subscription($data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_subscriptions';
        
        $wpdb->insert(
            $table,
            array(
                'subscription_id' => $data['subscription_id'],
                'customer_id' => isset($data['customer_id']) ? $data['customer_id'] : null,
                'customer_email' => isset($data['customer_email']) ? $data['customer_email'] : null,
                'plan_id' => isset($data['plan_id']) ? $data['plan_id'] : null,
                'status' => $data['status'],
                'amount' => $data['amount'],
                'currency' => $data['currency'],
                'interval' => isset($data['interval']) ? $data['interval'] : null,
                'next_payment_date' => isset($data['next_payment_date']) ? $data['next_payment_date'] : null,
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql'),
            ),
            array('%s', '%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        return $wpdb->insert_id;
    }
    
    public static function update_subscription($subscription_id, $data) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'onepay_subscriptions';
        
        $update_data = array(
            'updated_at' => current_time('mysql'),
        );
        
        if (isset($data['status'])) {
            $update_data['status'] = $data['status'];
        }
        
        if (isset($data['next_payment_date'])) {
            $update_data['next_payment_date'] = $data['next_payment_date'];
        }
        
        return $wpdb->update(
            $table,
            $update_data,
            array('subscription_id' => $subscription_id),
            array('%s', '%s', '%s'),
            array('%s')
        );
    }
}
