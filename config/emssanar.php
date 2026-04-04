<?php

return [
    'api_url' => env('EMSSANAR_API_URL', 'https://sagasb.emssanareps.co:8083/api/pqrd-externo/validar-afiliado'),
    'delay' => env('EMSSANAR_DELAY', 1000), // ms entre peticiones
    'timeout' => env('EMSSANAR_TIMEOUT', 30), // segundos
];
