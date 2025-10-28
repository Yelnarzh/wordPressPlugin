=== OnePay Payment Gateway ===
Contributors: yourname
Tags: payment, gateway, woocommerce, onepay, subscription
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Complete payment solution integrating OnePay API with WooCommerce. Supports one-time payments, subscriptions, refunds, and custom payment forms.

== Description ==

OnePay Payment Gateway is a comprehensive WordPress plugin that integrates the OnePay payment API with your WordPress and WooCommerce website.

**Features:**

* One-time payment processing
* Subscription payment handling with recurring billing
* Refund management from WordPress admin panel
* WooCommerce checkout integration
* Custom payment form shortcodes for any page
* Transaction logging and tracking
* Real-time webhook support for payment status updates
* Secure API key management
* Test mode for development

**Shortcodes:**

* `[onepay_payment_form amount="100" currency="USD" description="Product Payment"]` - Display a payment form
* `[onepay_subscription_form plan_id="plan_123" amount="29.99" interval="monthly"]` - Display a subscription form

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/onepay-payment-gateway` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Navigate to OnePay settings page to configure your API credentials
4. Enable the payment gateway in WooCommerce > Settings > Payments
5. Configure webhook URL in your OnePay dashboard: `https://yoursite.com/wp-json/onepay/v1/webhook`

== Frequently Asked Questions ==

= Do I need a OnePay account? =

Yes, you need to register for a OnePay merchant account at https://onepayltd.kz

= Does this plugin support test mode? =

Yes, you can enable test mode in the plugin settings to use the sandbox API.

= Can I process refunds? =

Yes, you can process full and partial refunds directly from the WooCommerce order page or from the OnePay admin panel.

== Changelog ==

= 1.0.0 =
* Initial release
* One-time payment processing
* Subscription support
* Refund management
* WooCommerce integration
* Custom shortcodes
* Webhook handling
