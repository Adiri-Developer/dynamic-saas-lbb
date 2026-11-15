<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Central Domains
    |--------------------------------------------------------------------------
    |
    | Domains where central features (like super admin panel and marketing site)
    | operate without tenant scoping.
    |
    */
    'central_domains' => array_filter([
        'localhost',
        '127.0.0.1',
        parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST),
        env('APP_CENTRAL_DOMAIN'),
    ]),

    /*
    |--------------------------------------------------------------------------
    | Exempt Subdomains
    |--------------------------------------------------------------------------
    |
    | Subdomains that should never be treated as tenant slugs.
    |
    */
    'exempt_subdomains' => [
        'www',
        'admin',
        'api',
        'mail',
        'superadmin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Roles
    |--------------------------------------------------------------------------
    |
    | Standard roles initialized for each tenant.
    |
    */
    'default_roles' => [
        'owner' => 'Pemilik Lembaga',
        'admin' => 'Administrator Lembaga',
        'tutor' => 'Tutor',
        'student' => 'Siswa',
        'parent' => 'Orang Tua',
        'member' => 'Team Member',
    ],
];
