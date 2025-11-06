<?php
// M-Pesa (Daraja) configuration. Use environment variables if available.

// Base URLs
$MPESA_ENV = getenv('MPESA_ENV') ?: 'sandbox'; // 'sandbox' or 'production'
$MPESA_BASE_URL = $MPESA_ENV === 'production'
    ? 'https://api.safaricom.co.ke'
    : 'https://sandbox.safaricom.co.ke';

// Credentials
$MPESA_CONSUMER_KEY = getenv('MPESA_CONSUMER_KEY') ?: '';
$MPESA_CONSUMER_SECRET = getenv('MPESA_CONSUMER_SECRET') ?: '';
$MPESA_SHORTCODE = getenv('MPESA_SHORTCODE') ?: ''; // PayBill or Till (BusinessShortCode)
$MPESA_PASSKEY = getenv('MPESA_PASSKEY') ?: '';

// Callback URL must be publicly accessible (use ngrok in dev)
$MPESA_CALLBACK_URL = getenv('MPESA_CALLBACK_URL') ?: '';

// Application settings
$MPESA_ACCOUNT_REF = getenv('MPESA_ACCOUNT_REF') ?: 'ECommerceOrder';
$MPESA_TXN_DESC = getenv('MPESA_TXN_DESC') ?: 'Order payment';

// Currency handling (M-Pesa uses KES)
$MPESA_CURRENCY = 'KES';

// Optional: local override file for development (do NOT commit secrets)
// Create config/mpesa.local.php with variables like $MPESA_CONSUMER_KEY, etc.
// This file will override the above values if present.
$mpesa_local = __DIR__ . '/mpesa.local.php';
if (file_exists($mpesa_local)) {
    include $mpesa_local;
}