<?php

return [
    'api_key' => env('FLOW_API_KEY'),
    'secret_key' => env('FLOW_SECRET_KEY'),
    'environment' => env('FLOW_ENVIRONMENT', 'sandbox'),
    'api_urls' => [
        'sandbox' => env('FLOW_API_URL_SANDBOX', 'https://sandbox.flow.cl/api'),
        'production' => env('FLOW_API_URL_PRODUCTION', 'https://www.flow.cl/api'),
    ],
    'api_url' => env('FLOW_ENVIRONMENT') === 'production'
        ? env('FLOW_API_URL_PRODUCTION', 'https://www.flow.cl/api')
        : env('FLOW_API_URL_SANDBOX', 'https://sandbox.flow.cl/api'),
];
