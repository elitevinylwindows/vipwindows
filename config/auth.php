<?php

return [
    'defaults' => [
        'guard' => 'vip',
        'passwords' => 'vip_users',
    ],

    'guards' => [
        'vip' => [
            'driver' => 'session',
            'provider' => 'vip_users',
        ],
    ],

    'providers' => [
        'vip_users' => [
            'driver' => 'eloquent',
            'model' => App\Models\VipUser::class,
        ],
    ],

    'passwords' => [
        'vip_users' => [
            'provider' => 'vip_users',
            'table' => 'password_resets',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
