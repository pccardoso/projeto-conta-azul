<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'contaazul' => [
        'domain' => env('CONTA_AZUL_DOMAIN'),
        "cobertura_total" => [
            'client_id' => env('CONTA_AZUL_CLIENT_ID_CE'),
            'client_secret' => env('CONTA_AZUL_CLIENT_SECRET_CE'),
        ],
        "meu_veiculo" => [
            'client_id' => env('CONTA_AZUL_CLIENT_ID'),
            'client_secret' => env('CONTA_AZUL_CLIENT_SECRET'),
        ],
    ],

    'efi' => [
        'certificate_path' => storage_path('app/private/efi/' . env('EFI_PIX_CERTIFICADO_FILENAME')),
        'domain_pix' => env('EFI_DOMAIN_PIX'),
        'domain_credit' => env('EFI_DOMAIN_CREDIT')
    ]

];
