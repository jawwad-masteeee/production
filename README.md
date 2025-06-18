# COD Verifier for WooCommerce

A comprehensive WordPress plugin that adds OTP and token payment verification for Cash on Delivery (COD) orders in WooCommerce with Twilio SMS integration.

## 🚀 Features

- **OTP Verification**: Phone number verification via Twilio SMS
- **Token Payment**: ₹1 payment verification to prevent fake orders
- **Test & Production Modes**: Easy testing before going live
- **Twilio Integration**: Reliable SMS delivery for OTP
- **Razorpay Integration**: Secure payment processing
- **Mobile Responsive**: Works on all devices
- **Easy Configuration**: Simple admin settings

## 📁 File Structure

```
/cod-verifier
├── cod-verifier.php                ← Main plugin file
├── /includes
│   ├── settings-page.php          ← Admin settings with Twilio config
│   ├── ajax-handlers.php          ← Handles OTP/Token AJAX logic
│   └── /twilio-sdk                 ← Twilio SDK directory (manual install)
│       └── /src/Twilio/            ← Twilio PHP SDK files
├── /assets
│   ├── script.js                  ← Frontend JavaScript
│   └── cod-verifier.css           ← Plugin styles
├── /templates
│   └── otp-box.php               ← Verification UI template
├── /languages
│   └── cod-verifier.pot          ← Translation file
└── readme.txt                    ← WordPress plugin readme
```

## 📦 Installation

### Step 1: Upload Plugin
1. Download/create the `cod-verifier` folder with all files
2. **Install Twilio SDK** (see Step 1.5 below)
3. Zip the entire `cod-verifier` folder
4. Go to WordPress Admin → Plugins → Add New → Upload Plugin
5. Upload the zip file and activate the plugin

### Step 1.5: Install Twilio SDK (CRITICAL)
1. Download Twilio PHP SDK from: https://github.com/twilio/twilio-php
2. Extract and copy the `src/Twilio/` folder to `includes/twilio-sdk/src/Twilio/`
3. Ensure this file exists: `includes/twilio-sdk/src/Twilio/autoload.php`

### Step 2: Configure Settings
1. Go to **WooCommerce → COD Verifier** in admin menu
2. **Enable Test Mode** (recommended for initial setup)
3. Configure Twilio Settings:
   - Account SID (from Twilio Console)
   - Auth Token (from Twilio Console)
   - Twilio Phone Number (with country code, e.g., +1234567890)
4. Choose verification options:
   - ✅ Enable OTP Verification
   - ✅ Enable Token Payment
5. Save settings

### Step 3: Test the Plugin
1. Go to your WooCommerce checkout page
2. Add any product to cart and proceed to checkout
3. Select **"Cash on Delivery"** as payment method
4. **Verification box should appear** below payment methods
5. Test OTP:
   - Enter a 10-digit phone number
   - Click "Send OTP"
   - **In test mode, OTP will be shown in alert popup**
   - Enter the OTP and verify
6. Test Token Payment:
   - Click "Pay ₹1 Token"
   - **In test mode, payment is simulated**
   - Check the confirmation checkbox
7. Place order - should complete successfully

## 🔧 Production Setup

### Twilio Configuration
1. Sign up at [Twilio](https://www.twilio.com/try-twilio)
2. Get a phone number from Twilio Console
3. Copy Account SID, Auth Token, and Phone Number to plugin settings
4. Test SMS delivery in Test Mode first

### Razorpay Configuration
1. Sign up at [Razorpay](https://razorpay.com)
2. Get your Key ID and Key Secret
3. Add keys in plugin settings
4. Test payment flow

### Switch to Production
1. Add all API keys in settings
2. **Disable Test Mode**
3. Save settings
4. Test complete flow with real SMS and payment

## 🧪 Testing Guide

### Test Mode Features
- **OTP Display**: OTP shown in JavaScript alert
- **SMS Simulation**: No real SMS sent
- **Payment Simulation**: No real money charged
- **All Functionality**: Complete verification flow

### Testing Steps
1. **Enable Test Mode** in settings
2. Go to checkout and select COD
3. **Verify UI appears** - verification box should show
4. **Test OTP Flow**:
   - Enter phone: `9876543210`
   - Click "Send OTP"
   - Check alert for OTP code
   - Enter OTP and verify
   - Should show "✓ Verified"
5. **Test Token Payment**:
   - Click "Pay ₹1 Token"
   - Should show "✓ Payment Complete"
   - Check confirmation checkbox
6. **Complete Order**:
   - Click "Place Order"
   - Order should complete successfully

### Troubleshooting

#### Verification Box Not Showing
- Check if Cash on Delivery is enabled in WooCommerce
- Ensure plugin is activated
- Check browser console for JavaScript errors
- Clear cache if using caching plugins

#### OTP Not Working
- Verify Twilio credentials in production
- Check phone number format (10 digits starting with 6-9)
- In test mode, check JavaScript alert for OTP
- Check WordPress error logs for Twilio errors

#### Twilio SDK Issues
- Ensure Twilio SDK is properly installed in `includes/twilio-sdk/src/Twilio/`
- Check that `autoload.php` exists in the Twilio directory
- Verify PHP version is 7.4 or higher
- Ensure cURL and OpenSSL extensions are enabled

#### Token Payment Issues
- Verify Razorpay keys in production
- Check if payment gateway is properly configured
- In test mode, payment should be simulated

#### Order Not Completing
- Check if both verifications are completed
- Look for validation errors on checkout page
- Check WordPress error logs

## 🔒 Security Features

- **Nonce Verification**: All AJAX requests secured
- **Session Management**: Secure session handling
- **Input Sanitization**: All inputs properly sanitized
- **OTP Expiration**: OTP expires after 5 minutes
- **Rate Limiting**: Prevents spam requests

## 🎨 Customization

### Styling
Edit `assets/cod-verifier.css` to customize appearance:
- Colors and themes
- Layout and spacing
- Mobile responsiveness
- Button styles

### Text/Language
Use WordPress translation functions to customize text:
- All strings are translatable
- Create `.po` files for different languages
- Modify text in template files

## 📞 Support

### Common Issues
1. **Plugin not working**: Check WooCommerce compatibility
2. **UI not showing**: Clear cache and check for conflicts
3. **API errors**: Verify API keys and test connectivity
4. **Session issues**: Check PHP session configuration
5. **Twilio errors**: Check SDK installation and credentials

### Debug Mode
Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

Check `/wp-content/debug.log` for errors.

## 📝 Changelog

### Version 1.1.0
- **BREAKING**: Migrated from Fast2SMS to Twilio
- Added Twilio SDK integration
- Improved error handling and logging
- Enhanced admin configuration notices
- Better production readiness checks

### Version 1.0.0
- Initial release
- OTP verification via Fast2SMS (deprecated)
- Token payment via Razorpay
- Test and production modes
- Mobile responsive design
- WordPress security standards

## 📄 License

This plugin is licensed under GPL v2 or later.

## 🤝 Contributing

1. Fork the repository
2. Create feature branch
3. Make changes
4. Test thoroughly
5. Submit pull request

---

**Ready to sell fake-order-free COD products with Twilio?** 🚀

Test the plugin thoroughly in Test Mode, then switch to Production Mode with your Twilio and Razorpay credentials for real customers!