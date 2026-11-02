<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Business profile
    |--------------------------------------------------------------------------
    |
    | Shown on the printable invoice. Override with environment variables.
    |
    */

    'business' => [
        'name' => env('BILLBOOK_BUSINESS_NAME', 'Okafor & Bello Studio'),
        'address' => env('BILLBOOK_BUSINESS_ADDRESS', '14 Admiralty Way, Lekki Phase 1, Lagos'),
        'email' => env('BILLBOOK_BUSINESS_EMAIL', 'accounts@okaforbello.studio'),
        'phone' => env('BILLBOOK_BUSINESS_PHONE', '+234 802 555 0147'),
        'tin' => env('BILLBOOK_BUSINESS_TIN', '23456789-0001'),
        'bank' => env('BILLBOOK_BANK_DETAILS', 'GTBank, Okafor & Bello Studio, 0123456789'),
    ],

    'vat_rate' => 0.075,

    'invoice_prefix' => 'INV-',

    'default_payment_terms' => 14,

    'per_page' => 10,

];
