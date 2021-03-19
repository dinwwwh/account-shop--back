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
            'creator' => makePower(true)
        ],

        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'waiting approve' to 'buying'
        |--------------------------------------------------------------------------
        */
        200 => [],


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
            'creator' => makePower(true)
        ],

        // Account info are safe
        // Only manager can read info
        480 => [
            'manager' => makePower(true),
            'buyer' => makePower(true),
            'creator' => makePower(true)
        ],


        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'buying' to 'bought'
        |--------------------------------------------------------------------------
        */
        600 => [],


        /*
        |--------------------------------------------------------------------------
        | Account bought
        |--------------------------------------------------------------------------
        */
        880 => [
            'manager' => makePower(true),
            'buyer' => makePower(true),
            'creator' => makePower(true)
        ],
    ],
    'default_status_code' => 0,
];
