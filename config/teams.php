<?php

return [

    'tenant_id' => env('TEAMS_TENANT_ID'),
    'client_id' => env('TEAMS_CLIENT_ID'),
    'client_secret' => env('TEAMS_CLIENT_SECRET'),
    'organizer_user_id' => env('TEAMS_ORGANIZER_USER_ID'),
    'redirect_uri' => env('TEAMS_REDIRECT_URI', env('APP_URL').'/integrations/microsoft/callback'),

    'scopes' => [
        'student' => 'openid profile email offline_access User.Read',
        'admin' => 'https://graph.microsoft.com/.default',
    ],

    'graph_base' => 'https://graph.microsoft.com/v1.0',
    'authority' => 'https://login.microsoftonline.com',

];
