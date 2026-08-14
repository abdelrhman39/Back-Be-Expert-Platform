<?php

return [

    'secret_key' => env('MOYASAR_SECRET_KEY', env('MOYASAR_API_KEY')),

    'publishable_key' => env('MOYASAR_PUBLISHABLE_KEY'),

    'webhook_secret' => env('MOYASAR_WEBHOOK_SECRET'),

    'currency' => env('MOYASAR_CURRENCY', 'SAR'),

];
