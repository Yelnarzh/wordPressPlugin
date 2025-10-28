# OnePay Payment Gateway for WordPress

A comprehensive WordPress plugin that integrates the OnePay payment API with WooCommerce, providing support for one-time payments, subscriptions, refunds, and custom payment forms.

## Features

- **One-time Payment Processing**: Accept credit/debit card payments via OnePay API
- **Subscription Management**: Handle recurring payments with automatic billing
- **Refund Management**: Process full and partial refunds from WordPress admin
- **WooCommerce Integration**: Seamless checkout experience for WooCommerce stores
- **Custom Payment Forms**: Use shortcodes to add payment forms anywhere on your site
- **Transaction Logging**: Complete transaction history with detailed status tracking
- **Webhook Support**: Real-time payment status updates via webhooks
- **Test Mode**: Sandbox environment for testing before going live
- **PCI DSS Compliant**: No card data stored on your server - uses OnePay's hosted payment page
- **Secure**: Webhook signature verification, API key management, and security best practices

## Installation

### Upload via WordPress Admin

1. Download the plugin ZIP file
2. Go to WordPress Admin > Plugins > Add New
3. Click "Upload Plugin" and choose the ZIP file
4. Click "Install Now" and then "Activate"

### Manual Installation

1. Upload the `onepay-payment-gateway` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress

## Configuration

### 1. Get Your API Credentials

1. Register for a OnePay merchant account at https://onepayltd.kz
2. Obtain your API key and API secret from the OnePay dashboard

### 2. Configure the Plugin

1. Navigate to **OnePay** in WordPress admin menu
2. Enter your API credentials:
   - API Key
   - API Secret (optional)
   - Webhook Secret (for webhook signature verification)
3. Enable/disable test mode as needed
4. Save settings

### 3. Configure Webhooks (REQUIRED)

**IMPORTANT**: Webhook secret is mandatory for security. Without it, webhooks will be rejected.

1. Copy the webhook URL from settings: `https://yoursite.com/wp-json/onepay/v1/webhook`
2. Log in to OnePay dashboard and navigate to webhook settings
3. Add the webhook URL
4. Generate a strong webhook secret (min 32 characters recommended)
5. Copy the secret and paste it in WordPress plugin settings (OnePay → Settings → Webhook Secret)
6. Save settings in both platforms

### 4. Enable WooCommerce Gateway

1. Go to **WooCommerce > Settings > Payments**
2. Enable "OnePay" payment method
3. Configure the display title and description
4. Save changes

## Usage

### WooCommerce Checkout

Once enabled, customers will see OnePay as a payment option during checkout. The plugin handles:
- Payment creation
- Redirect to payment page
- Payment confirmation
- Order completion
- Status updates via webhooks

### Payment Form Shortcode

Display a custom payment form anywhere on your site:

```
[onepay_payment_form amount="100" currency="USD" description="Product Payment" button_text="Pay Now"]
```

**Parameters:**
- `amount` (optional): Fixed amount or leave empty for user input
- `currency` (default: USD): Payment currency
- `description` (optional): Payment description
- `button_text` (default: "Pay Now"): Submit button text

### Subscription Form Shortcode

Display a subscription form:

```
[onepay_subscription_form plan_id="plan_123" amount="29.99" interval="monthly" button_text="Subscribe"]
```

**Parameters:**
- `plan_id` (required): OnePay subscription plan ID
- `amount` (optional): Display amount
- `interval` (optional): Billing interval (e.g., "monthly", "yearly")
- `button_text` (default: "Subscribe"): Submit button text

## Admin Features

### Transaction Management

View all transactions at **OnePay > Transactions**:
- Transaction ID
- Order ID
- Payment ID
- Amount and currency
- Status (succeeded, failed, pending, canceled)
- Transaction type (payment, refund)
- Date created

### Subscription Management

Manage subscriptions at **OnePay > Subscriptions**:
- Subscription ID
- Customer email
- Plan ID
- Amount and currency
- Status (active, canceled)
- Next payment date
- Cancel active subscriptions

### Refunds

Process refunds directly from WooCommerce orders:
1. Open the order in WooCommerce
2. Click "Refund" button
3. Enter refund amount (full or partial)
4. Add reason (optional)
5. Click "Refund via OnePay"

## File Structure

```
onepay-payment-gateway/
├── admin/
│   ├── css/
│   │   └── admin.css
│   ├── js/
│   │   └── admin.js
│   └── class-onepay-admin.php
├── includes/
│   ├── class-onepay-main.php
│   ├── class-onepay-api.php
│   ├── class-onepay-db.php
│   ├── class-onepay-logger.php
│   ├── class-onepay-webhook.php
│   ├── class-onepay-subscriptions.php
│   └── class-onepay-wc-gateway.php
├── public/
│   ├── css/
│   │   └── public.css
│   ├── js/
│   │   └── public.js
│   └── class-onepay-shortcodes.php
├── logs/
│   └── .htaccess
├── onepay-payment-gateway.php
├── readme.txt
└── README.md
```

## Webhook Events

The plugin handles the following webhook events:

- `payment.succeeded`: Payment completed successfully
- `payment.failed`: Payment failed
- `payment.canceled`: Payment was canceled
- `refund.succeeded`: Refund processed successfully
- `refund.failed`: Refund failed
- `subscription.created`: New subscription created
- `subscription.updated`: Subscription updated
- `subscription.canceled`: Subscription canceled
- `subscription.payment_succeeded`: Recurring payment succeeded
- `subscription.payment_failed`: Recurring payment failed

## Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- WooCommerce 5.0 or higher (for e-commerce features)
- OnePay merchant account
- **SSL certificate (REQUIRED for production)** - HTTPS is mandatory for payment processing

## Security

This plugin is designed with PCI DSS compliance in mind:

- **No card data on your server**: Card numbers and CVV are never transmitted to or stored on WordPress
- **Hosted payment page**: Customers enter card details on OnePay's secure PCI-compliant page
- **Webhook verification**: All webhooks require HMAC signature verification (mandatory)
- **Secure API communication**: All API calls use HTTPS with API key authentication

For detailed security information, see [SECURITY.md](SECURITY.md)

## Support

For support, please contact:
- Email: support@yourcompany.com
- OnePay API Documentation: https://stoplight.onepayltd.kz
- Security Issues: security@yourcompany.com (private disclosure)

## License

This plugin is licensed under the GPL v2 or later.

## Changelog

### 1.0.0
- Initial release
- One-time payment processing
- Subscription support
- Refund management
- WooCommerce integration
- Custom shortcodes
- Webhook handling
- Transaction logging
