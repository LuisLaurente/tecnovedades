<?php

return [
    'google' => [
        'clientId'     => '', // Reemplaza por tu Client ID de Google
        'clientSecret' => '', // Reemplaza por tu Client Secret de Google
        'redirectUri'  => 'http://localhost/tecnovedades/public/auth/google-callback', // Cambia si usas otro dominio
    ],
    'facebook' => [
        'clientId'     => '', // Tu App ID de Facebook
        'clientSecret' => '', // Tu App Secret de Facebook
        'redirectUri'  => 'http://localhost/tecnovedades/public/auth/facebook-callback', // Cambia si usas otro dominio
    ],
];
