<?php

use App\Helpers\AccountConfigHelper;

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
            'manager' => AccountConfigHelper::makePower(true),
            'buyer' => AccountConfigHelper::makePower(true),
            'creator' => AccountConfigHelper::makePower(true),
            'priority' => 100,
        ],

        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'waiting approve' to 'buying'
        |--------------------------------------------------------------------------
        */
        200 => [
            'manager' => AccountConfigHelper::makePower(false),
            'buyer' => AccountConfigHelper::makePower(false),
            'creator' => AccountConfigHelper::makePower(false),
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
            'manager' => AccountConfigHelper::makePower(true),
            'buyer' => AccountConfigHelper::makePower(true),
            'creator' => AccountConfigHelper::makePower(true),
            'priority' => 200,
        ],

        // Account info are safe
        // Only manager can read info
        480 => [
            'manager' => AccountConfigHelper::makePower(true),
            'buyer' => AccountConfigHelper::makePower(true),
            'creator' => AccountConfigHelper::makePower(true),
            'priority' => 300,
        ],


        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'buying' to 'bought'
        |--------------------------------------------------------------------------
        */
        600 => [
            'manager' => AccountConfigHelper::makePower(false),
            'buyer' => AccountConfigHelper::makePower(false),
            'creator' => AccountConfigHelper::makePower(false),
            'priority' => -1,
        ],


        /*
        |--------------------------------------------------------------------------
        | Account bought
        |--------------------------------------------------------------------------
        */
        880 => [
            'manager' => AccountConfigHelper::makePower(true),
            'buyer' => AccountConfigHelper::makePower(true),
            'creator' => AccountConfigHelper::makePower(true),
            'priority' => -1,
        ],
    ],
    'default_status_code' => 0,
];
