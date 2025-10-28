# Security Guidelines for OnePay Payment Gateway

## PCI DSS Compliance

This plugin is designed with PCI DSS compliance in mind by **NOT** handling sensitive cardholder data (PAN, CVV/CVC) directly on your server.

### How It Works

1. **Hosted Payment Page**: The plugin creates payment intents via the OnePay API and redirects customers to OnePay's PCI-compliant hosted payment page
2. **No Card Data Storage**: Card numbers, expiry dates, and CVV codes are never transmitted to or stored on your WordPress server
3. **Tokenization**: OnePay handles tokenization and secure card data storage
4. **Webhook Verification**: All payment status updates are verified using HMAC signatures

## Security Features

### 1. Webhook Authentication

**REQUIRED**: You must configure a webhook secret in both:
- OnePay dashboard webhook settings
- WordPress plugin settings (OnePay → Settings)

Without a webhook secret, all webhooks will be rejected to prevent unauthorized order status manipulation.

```php
// Webhook verification is mandatory
if (empty($webhook_secret)) {
    // Rejects the webhook
    return false;
}
```

### 2. API Key Protection

- API keys are stored in WordPress options (database)
- Never expose API keys in frontend JavaScript
- Use environment variables for additional security (recommended for production)
- Log files containing API responses are protected with `.htaccess`

### 3. Input Sanitization

All user inputs are sanitized using WordPress functions:
- `sanitize_text_field()` for text inputs
- `sanitize_email()` for email addresses
- `esc_attr()`, `esc_html()`, `esc_url()` for output escaping

### 4. AJAX Security

All AJAX requests use WordPress nonces:
```php
check_ajax_referer('onepay_payment_nonce', 'nonce');
```

### 5. SQL Injection Protection

All database queries use prepared statements:
```php
$wpdb->prepare("SELECT * FROM $table WHERE payment_id = %s", $payment_id);
```

## Security Best Practices

### For Merchants

1. **Always Use HTTPS**
   - SSL certificate is REQUIRED for production
   - Never process payments over HTTP

2. **Secure Your WordPress Installation**
   - Keep WordPress, plugins, and themes updated
   - Use strong admin passwords
   - Enable two-factor authentication
   - Limit login attempts
   - Regular security audits

3. **API Credentials**
   - Never commit API keys to version control
   - Use different API keys for test and production
   - Rotate API keys periodically
   - Restrict API key permissions in OnePay dashboard

4. **Webhook Configuration**
   - **ALWAYS** set a strong webhook secret (min 32 characters)
   - Use HTTPS webhook URLs only
   - Monitor webhook logs for suspicious activity
   - Verify webhook endpoints are not publicly documented

5. **File Permissions**
   - Logs directory: 755 (protected by .htaccess)
   - PHP files: 644
   - Directories: 755
   - wp-config.php: 600

6. **Database Security**
   - Regular backups
   - Use strong database passwords
   - Restrict database user privileges
   - Never use 'root' for WordPress database

### For Developers

1. **Never Log Sensitive Data**
   ```php
   // ❌ BAD - Don't log card numbers or CVV
   OnePay_Logger::log('Card: ' . $card_number);
   
   // ✅ GOOD - Log only safe identifiers
   OnePay_Logger::log('Payment ID: ' . $payment_id);
   ```

2. **Validate All Inputs**
   ```php
   // Always validate and sanitize
   $email = sanitize_email($_POST['email']);
   if (!is_email($email)) {
       wp_send_json_error('Invalid email');
   }
   ```

3. **Use WordPress Functions**
   ```php
   // Use WordPress HTTP API, not cURL directly
   $response = wp_remote_post($url, $args);
   ```

4. **Check Capabilities**
   ```php
   // Verify user permissions
   if (!current_user_can('manage_options')) {
       wp_die('Unauthorized');
   }
   ```

## What This Plugin Does NOT Do

- ❌ Store credit card numbers
- ❌ Store CVV/CVC codes
- ❌ Transmit card data through your server
- ❌ Handle raw card data in any form
- ❌ Store full card details in WordPress database

## What This Plugin DOES Do

- ✅ Creates payment intents via API
- ✅ Redirects to OnePay's secure hosted page
- ✅ Receives webhook notifications (verified)
- ✅ Updates order statuses
- ✅ Logs transaction metadata (no card data)

## Incident Response

If you suspect a security issue:

1. **Immediately**:
   - Rotate all API keys in OnePay dashboard
   - Update webhook secret
   - Check transaction logs for anomalies

2. **Investigate**:
   - Review WordPress access logs
   - Check for unauthorized admin users
   - Scan for malware/backdoors
   - Review recent plugin/theme changes

3. **Report**:
   - Contact OnePay support if API keys were compromised
   - Document the incident
   - Notify affected customers if necessary (GDPR/PCI requirements)

## Regular Security Checklist

- [ ] WordPress core is up to date
- [ ] All plugins are up to date
- [ ] SSL certificate is valid
- [ ] Webhook secret is configured and strong
- [ ] API keys are different for test/production
- [ ] Logs directory is protected
- [ ] No sensitive data in error logs
- [ ] Database backups are automated
- [ ] Admin accounts use strong passwords
- [ ] Two-factor authentication enabled
- [ ] File permissions are correct
- [ ] Regular security scans performed

## Additional Resources

- [PCI DSS Quick Reference Guide](https://www.pcisecuritystandards.org/)
- [WordPress Security Best Practices](https://wordpress.org/support/article/hardening-wordpress/)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [OnePay API Security Documentation](https://stoplight.onepayltd.kz)

## Reporting Security Vulnerabilities

If you discover a security vulnerability in this plugin:
1. Do NOT open a public GitHub issue
2. Email security@yourcompany.com with details
3. Include steps to reproduce
4. Allow reasonable time for a fix before public disclosure
