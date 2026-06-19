<?php

return [

    // Get a free key at https://aistudio.google.com/apikey
    'api_key' => env('GEMINI_API_KEY','AQ.Ab8RN6KwqE5DZ7V6Ivn9yAjar1v4UBMpURo36OIzPY1athRYFQ'),

    // gemma-4-26b-a4b-it = fast/cheap MoE model, good default for course drafting.
    // gemma-4-31b-it     = highest quality dense model, slower/more expensive.
    'model' => env('GEMMA_MODEL', 'gemma-4-26b-a4b-it'),

    'base_url' => 'https://generativelanguage.googleapis.com/v1beta/models',

];
