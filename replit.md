# OnePay Payment Gateway WordPress Plugin

## Overview

This project is a comprehensive WordPress plugin that integrates the OnePay payment API with WordPress and WooCommerce. The plugin provides complete payment processing capabilities including one-time payments, subscription management, refunds, and custom payment forms.

## Purpose

The OnePay Payment Gateway plugin enables WordPress websites to:
- Accept payments through the OnePay payment processing API
- Process one-time credit/debit card transactions
- Manage recurring subscription payments
- Handle full and partial refunds
- Integrate seamlessly with WooCommerce checkout
- Display custom payment forms via shortcodes
- Track all transactions in WordPress admin

## Project Architecture

### Core Components

1. **Main Plugin File** (`onepay-payment-gateway.php`)
   - Plugin initialization and WordPress hooks
   - Defines constants and plugin metadata
   - Handles activation/deactivation

2. **API Integration** (`includes/class-onepay-api.php`)
   - Complete OnePay API client implementation
   - Handles payment creation, capture, refunds
   - Manages subscriptions and customers
   - Secure API communication with error handling

3. **Database Layer** (`includes/class-onepay-db.php`)
   - Custom database tables for transactions and subscriptions
   - Transaction logging and retrieval
   - Subscription status tracking

4. **WooCommerce Gateway** (`includes/class-onepay-wc-gateway.php`)
   - Payment gateway integration with WooCommerce
   - Checkout processing and order management
   - Refund handling from order interface

5. **Webhook Handler** (`includes/class-onepay-webhook.php`)
   - REST API endpoint for OnePay webhooks
   - Real-time payment status updates
   - Event processing for all payment states

6. **Subscription Manager** (`includes/class-onepay-subscriptions.php`)
   - Recurring payment handling
   - Subscription lifecycle management
   - Automatic renewal checking via WordPress cron

7. **Admin Interface** (`admin/class-onepay-admin.php`)
   - Settings page for API configuration
   - Transaction management interface
   - Subscription oversight and cancellation

8. **Shortcodes** (`public/class-onepay-shortcodes.php`)
   - Payment form shortcode
   - Subscription form shortcode
   - AJAX payment processing

## Features Implemented

### Core Features
- ✅ One-time payment processing
- ✅ Subscription payment handling with recurring billing
- ✅ Full and partial refund management
- ✅ WooCommerce checkout integration
- ✅ Custom payment form shortcodes
- ✅ Transaction logging and database storage
- ✅ Real-time webhook event handling
- ✅ Secure API key management
- ✅ Test mode for development
- ✅ Comprehensive error logging

### Admin Features
- ✅ API configuration interface
- ✅ Transaction history viewer
- ✅ Subscription management
- ✅ Webhook URL display
- ✅ Logging controls

### Security Features
- ✅ Webhook signature verification
- ✅ API key encryption
- ✅ Protected log files (.htaccess)
- ✅ AJAX nonce verification
- ✅ Input sanitization and validation

## Installation

1. **Package the Plugin**
   - The plugin is located in the `onepay-payment-gateway/` folder
   - Zip this folder for distribution

2. **Install in WordPress**
   - Upload via WordPress admin (Plugins → Add New → Upload)
   - Or copy folder to `/wp-content/plugins/`
   - Activate through WordPress admin

3. **Configure**
   - Get API credentials from https://onepayltd.kz
   - Navigate to OnePay settings in WordPress admin
   - Enter API key, secret, and webhook configuration
   - Enable in WooCommerce → Settings → Payments

## Usage

### Shortcodes

**Payment Form:**
```
[onepay_payment_form amount="100" currency="USD" description="Product Payment"]
```

**Subscription Form:**
```
[onepay_subscription_form plan_id="plan_123" amount="29.99" interval="monthly"]
```

### WooCommerce Integration
- Automatically appears as payment method at checkout
- Supports payment processing, order completion, and refunds

### Webhook URL
Configure in OnePay dashboard:
```
https://yoursite.com/wp-json/onepay/v1/webhook
```

## Database Schema

**Transactions Table:**
- transaction_id (unique identifier)
- order_id (WooCommerce order reference)
- payment_id (OnePay payment ID)
- customer_email
- amount, currency
- status (succeeded, failed, pending, canceled)
- type (payment, refund)
- metadata (JSON)
- created_at, updated_at

**Subscriptions Table:**
- subscription_id (OnePay subscription ID)
- customer_id, customer_email
- plan_id
- status (active, canceled)
- amount, currency, interval
- next_payment_date
- metadata (JSON)
- created_at, updated_at

## API Integration

The plugin integrates with OnePay API endpoints:
- POST `/payments` - Create payment
- POST `/payments/{id}/capture` - Capture payment
- POST `/refunds` - Create refund
- POST `/subscriptions` - Create subscription
- POST `/subscriptions/{id}/cancel` - Cancel subscription
- GET `/payments/{id}` - Retrieve payment
- GET `/refunds/{id}` - Retrieve refund

## File Structure

```
onepay-payment-gateway/
├── admin/              # Admin interface files
├── includes/           # Core plugin classes
├── public/             # Frontend forms and assets
├── logs/               # Log files (protected)
├── *.php               # Main plugin file
└── documentation files
```

## Requirements

- WordPress 5.8+
- PHP 7.4+
- WooCommerce 5.0+ (for e-commerce features)
- OnePay merchant account
- SSL certificate (required for production)

## Recent Changes

- 2025-10-31: Added WooCommerce HPOS compatibility
  - Declared compatibility with High-Performance Order Storage
  - Plugin now works seamlessly with WooCommerce's modern order tables
  - No compatibility warnings in WooCommerce settings

- 2025-10-31: Fixed database schema error
  - Renamed 'interval' column to 'billing_interval' to avoid MySQL reserved keyword
  - Resolved SQL syntax error in wp_onepay_subscriptions table creation

- 2025-10-31: Fixed activation error
  - Resolved "Class 'OnePay_DB' not found" fatal error during plugin activation
  - Added required class file loading in activate() method
  - Plugin now activates successfully in WordPress

- 2025-10-28: Initial plugin development completed
  - Core plugin structure implemented
  - OnePay API integration
  - WooCommerce gateway integration
  - Subscription management
  - Webhook handling
  - Admin interface
  - Custom shortcodes
  - Complete documentation

## User Preferences

None specified yet.

## Notes

- This is a WordPress plugin, not a standalone application
- Requires WordPress environment for testing
- All API calls use WordPress HTTP API
- Follows WordPress coding standards
- Internationalization ready (text domain: 'onepay-payment-gateway')
- Logs stored in plugin directory with .htaccess protection

## Next Steps (Future Enhancements)

- Advanced transaction filtering and search
- Export transactions to CSV
- Custom email templates for payment notifications
- Multi-currency support enhancements
- Payment analytics dashboard
- Customer payment method management UI
- Webhook event retry mechanism
- Partial refund UI improvements
