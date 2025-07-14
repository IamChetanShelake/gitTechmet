<?php

return [
    'merchant_id' => env('PAYPHI_MERCHANT_ID', 'T_03338'),
    'secret' => env('PAYPHI_SECRET', 'abc'),
    'return_url' => env('PAYPHI_RETURN_URL', 'http://127.0.0.1:8000/payment/response'),
    'initiate_url' => env('PAYPHI_INITIATE_URL', 'https://qa.phicommerce.com/pg/api/v2/initiateSale'),
    'command_url' => env('PAYPHI_COMMAND_URL', 'https://qa.phicommerce.com/pg/api/command'),
    'currency_code' => '356', // INR
    'pay_type' => '0', // Redirect
    'transaction_type' => 'SALE',
    'refund_type' => 'REFUND',
    'status_type' => 'STATUS',
];