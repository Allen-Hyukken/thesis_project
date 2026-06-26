<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EDITH — AI API Keys
    |--------------------------------------------------------------------------
    | Add as many keys as you have. EDITH will exhaust all models under key 1
    | before moving to key 2, and so on. Keys cycle back around once all are
    | exhausted (daily quotas reset at midnight UTC).
    |
    | In your .env:
    |   EDITH_API_KEY_1=AIza...
    |   EDITH_API_KEY_2=AIza...
    |   EDITH_API_KEY_3=AIza...  (add as many as needed)
    |
    | Get free keys at: https://aistudio.google.com/apikey
    |--------------------------------------------------------------------------
    */
    'api_keys' => array_values(array_filter([
        env('EDITH_API_KEY_1'),
        env('EDITH_API_KEY_2'),
        env('EDITH_API_KEY_3'),
        env('EDITH_API_KEY_4'),
        env('EDITH_API_KEY_5'),
        env('EDITH_API_KEY_6'),
        env('EDITH_API_KEY_7'),
        env('EDITH_API_KEY_8'),
        env('EDITH_API_KEY_9'),
        env('EDITH_API_KEY_10'),
    ])),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    */
    'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models',

];
