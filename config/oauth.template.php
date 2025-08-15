<?php

return [
    'google' => [
        'clientId'     => '251891397166-j8n5uev3eb5ttdqfa5dbq3dvjk1vo38r.apps.googleusercontent.com', // Reemplaza por tu Client ID de Google
        'clientSecret' => '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', // Reemplaza por tu Client Secret de Google
        'redirectUri'  => 'http://localhost/tecnovedades/public/auth/google-callback', // Cambia si usas otro dominio
    ],
    'facebook' => [
        'clientId'     => '', // Tu App ID de Facebook
        'clientSecret' => '', // Tu App Secret de Facebook
        'redirectUri'  => 'http://localhost/tecnovedades/public/auth/facebook-callback', // Cambia si usas otro dominio
    ],
];
