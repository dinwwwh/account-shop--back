<?php

function makePower(...$values)
{
    return [
        'can_read_sensitive_info' => $values[0] ?? false,
        'can_update_sensitive_info' => $values[0] ?? false,
    ];
}

return [
    'status_codes' => [
        /*
        |--------------------------------------------------------------------------
        | Account waiting approve
        |--------------------------------------------------------------------------
        */
        // Account info are dangerous
        // Creator and manage can read sensitive info of account.
        0 => [
            'manager' => makePower(true),
            'buyer' => makePower(true),
            'creator' => makePower(true),
            'priority' => 100,
        ],

        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'waiting approve' to 'buying'
        |--------------------------------------------------------------------------
        */
        200 => [
            'manager' => makePower(false),
            'buyer' => makePower(false),
            'creator' => makePower(false),
            'priority' => -1,
        ],


        /*
        |--------------------------------------------------------------------------
        | Account buying
        |--------------------------------------------------------------------------
        */
        // Account info are dangerous
        // Creator and manager can read sensitive info of account.
        440 => [
            'manager' => makePower(true),
            'buyer' => makePower(true),
            'creator' => makePower(true),
            'priority' => 200,
        ],

        // Account info are safe
        // Only manager can read info
        480 => [
            'manager' => makePower(true),
            'buyer' => makePower(true),
            'creator' => makePower(true),
            'priority' => 300,
        ],


        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'buying' to 'bought'
        |--------------------------------------------------------------------------
        */
        600 => [
            'manager' => makePower(false),
            'buyer' => makePower(false),
            'creator' => makePower(false),
            'priority' => -1,
        ],


        /*
        |--------------------------------------------------------------------------
        | Account bought
        |--------------------------------------------------------------------------
        */
        880 => [
            'manager' => makePower(true),
            'buyer' => makePower(true),
            'creator' => makePower(true),
            'priority' => -1,
        ],
    ],
    'default_status_code' => 0,
];
