# OnePay Payment Gateway - Installation Guide

## Prerequisites

Before installing the OnePay Payment Gateway plugin, ensure you have:

1. **WordPress Installation**: WordPress 5.8 or higher
2. **PHP Version**: PHP 7.4 or higher
3. **WooCommerce** (optional): Version 5.0 or higher (required for e-commerce features)
4. **OnePay Account**: Register at https://onepayltd.kz to get your API credentials

## Installation Steps

### Method 1: Upload via WordPress Admin (Recommended)

1. **Package the Plugin**
   - Zip the entire `onepay-payment-gateway` folder
   - Make sure the folder structure is maintained

2. **Upload to WordPress**
   - Log in to your WordPress admin dashboard
   - Navigate to **Plugins > Add New**
   - Click **Upload Plugin**
   - Choose the ZIP file you created
   - Click **Install Now**

3. **Activate the Plugin**
   - After installation completes, click **Activate Plugin**
   - You should see "Plugin activated successfully" message

### Method 2: Manual Installation via FTP

1. **Connect to Your Server**
   - Use FTP client (FileZilla, WinSCP, etc.)
   - Connect to your WordPress hosting server

2. **Upload Plugin Files**
   - Navigate to `/wp-content/plugins/` directory
   - Upload the entire `onepay-payment-gateway` folder

3. **Set Permissions**
   - Ensure the `logs` directory is writable (chmod 755 or 775)
   - Set proper file permissions (644 for files, 755 for directories)

4. **Activate via WordPress**
   - Go to WordPress admin > Plugins
   - Find "OnePay Payment Gateway"
   - Click **Activate**

## Configuration

### Step 1: Basic Settings

1. Navigate to **OnePay** in your WordPress admin menu
2. Enter your OnePay API credentials:
   - **Test Mode**: Enable for testing with sandbox API
   - **API Key**: Your OnePay API key
   - **API Secret**: Your OnePay API secret (if required)
   - **Webhook Secret**: Secret for webhook signature verification

3. Click **Save Changes**

### Step 2: Webhook Configuration

1. Copy the webhook URL shown in settings:
   ```
   https://yoursite.com/wp-json/onepay/v1/webhook
   ```

2. Log in to your OnePay dashboard
3. Navigate to webhook settings
4. Add the webhook URL
5. Configure webhook events to send:
   - payment.succeeded
   - payment.failed
   - payment.canceled
   - refund.succeeded
   - subscription.created
   - subscription.canceled
   - subscription.payment_succeeded

6. Generate and copy the webhook secret
7. Paste the webhook secret in plugin settings

### Step 3: WooCommerce Integration (Optional)

1. Go to **WooCommerce > Settings > Payments**
2. Find "OnePay" in the payment methods list
3. Click **Enable**
4. Click **Manage** to configure:
   - **Title**: Display name (e.g., "Credit Card / Debit Card")
   - **Description**: Customer-facing description
5. Save changes

## Testing

### Test Mode

1. Enable **Test Mode** in OnePay settings
2. Use OnePay test card numbers:
   - Success: 4242 4242 4242 4242
   - Decline: 4000 0000 0000 0002
   - Any future expiry date and CVC

3. Create a test order in WooCommerce or use a payment shortcode
4. Complete payment with test card
5. Verify transaction appears in **OnePay > Transactions**

### Shortcode Testing

Add to any page or post:

```
[onepay_payment_form amount="10.00" currency="USD" description="Test Payment"]
```

Preview the page and test the payment form.

## Troubleshooting

### Plugin Not Appearing

- Clear WordPress cache
- Deactivate and reactivate the plugin
- Check PHP error logs for issues

### Payments Not Processing

1. Verify API credentials are correct
2. Check if test mode matches your API environment
3. Review logs at `/wp-content/plugins/onepay-payment-gateway/logs/onepay.log`
4. Enable WordPress debug mode to see errors

### Webhooks Not Working

1. Verify webhook URL is publicly accessible (not localhost)
2. Check webhook secret matches between OnePay and plugin settings
3. Test webhook endpoint manually:
   ```bash
   curl -X POST https://yoursite.com/wp-json/onepay/v1/webhook
   ```

### Database Tables Not Created

- Deactivate and reactivate the plugin
- Tables will be created on activation:
  - `wp_onepay_transactions`
  - `wp_onepay_subscriptions`

## Logging

Enable logging for debugging:

1. Go to **OnePay Settings**
2. Check **Enable Logging**
3. Logs are written to: `/wp-content/plugins/onepay-payment-gateway/logs/onepay.log`
4. Review logs for API requests, responses, and errors

## Security Recommendations

1. **Use HTTPS**: Always use SSL certificate for production
2. **Secure API Keys**: Never commit API keys to version control
3. **File Permissions**: Keep log files protected (already configured with .htaccess)
4. **Regular Updates**: Keep WordPress, WooCommerce, and this plugin updated
5. **Webhook Verification**: Always use webhook secret for signature verification

## Going Live

Before going live:

1. **Disable Test Mode** in plugin settings
2. **Switch to Production API Keys** in OnePay dashboard
3. **Update webhook URL** to use production domain
4. **Test with real payment** (small amount)
5. **Monitor transactions** in admin panel
6. **Verify webhook delivery** in OnePay dashboard

## Support

For technical support:
- Plugin Issues: Check plugin documentation
- OnePay API: https://stoplight.onepayltd.kz
- WordPress Support: https://wordpress.org/support/

## Next Steps

- Customize payment form styling in `/public/css/public.css`
- Add custom transaction statuses with WordPress filters
- Integrate with membership plugins for subscriptions
- Set up email notifications for payment events
- Configure refund policies in WooCommerce
