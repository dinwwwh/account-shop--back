<?php

return [
    'status_codes' => [
        /*
        |--------------------------------------------------------------------------
        | Account waiting approve
        | 0 -> 99
        |--------------------------------------------------------------------------
        */
        // Account info are dangerous
        // Creator and manage can read sensitive info of account.
        0,

        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'waiting approve' to 'buying'
        | 200 -> 299
        |--------------------------------------------------------------------------
        */
        200,

        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'waiting approve' to 'reject' by approver
        | 300 -> 399
        |--------------------------------------------------------------------------
        */
        300, #From 200 status

        /*
        |--------------------------------------------------------------------------
        | Account buying
        | 400 -> 499
        |--------------------------------------------------------------------------
        */
        // Account info are dangerous
        // Creator and manager can read sensitive info of account.
        440,

        // Account info are safe
        // Only manager can read info
        480,


        /*
        |--------------------------------------------------------------------------
        | Transitioning account from 'buying' to 'bought'
        | 600 -> 699
        |--------------------------------------------------------------------------
        */
        600,


        /*
        |--------------------------------------------------------------------------
        | Account bought
        | 800 -> 899
        |--------------------------------------------------------------------------
        */

        # form 440
        840,

        # form 480
        880,

        /*
        |--------------------------------------------------------------------------
        | Account bought and was confirm NOT ok by buyer
        | 900 -> 999
        |--------------------------------------------------------------------------
        */

        # form 840
        1040,

        # form 880
        1080,

        /*
        |--------------------------------------------------------------------------
        | Account bought and was confirm OK by buyer -> done
        | 1000 -> 1099
        |--------------------------------------------------------------------------
        */

        # form 840
        1140,

        # form 880
        1180,
    ],

    'buyable_status_codes' => [440, 480],
    'status_codes_pending_approval' => [0],
    'status_codes_approving' => [200],
    'status_codes_after_created' => [0, 440, 480],
    'status_codes_after_approved' => [440, 480],
    'status_codes_after_approved_fail' => [300],

    'buyer' => [
        'readable_login_infos_status_codes' => [840, 880, 1140, 1180],
        'readable_account_infos_status_codes' => [1140, 1180],
    ],
    'creator' => [
        'readable_login_infos_status_codes' => [0, 440, 300],
        'updatable_login_infos_status_codes' => [0, 440],
        'readable_account_infos_status_codes' => [0, 440, 300],
        'updatable_account_infos_status_codes' => [0, 440],
        'updatable_game_infos_status_codes' => [0, 440],
        'updatable_cost_status_codes' => [0, 440],
        'updatable_images_status_codes' => [0, 440],
    ],
    'approver' => [
        'readable_login_infos_status_codes' => [200],
        'updatable_login_infos_status_codes' => [200],
        'readable_account_infos_status_codes' => [200],
        'updatable_account_infos_status_codes' => [200],
        'updatable_game_infos_status_codes' => [200],
        'updatable_cost_status_codes' => [200],
        'updatable_images_status_codes' => [200],
    ],
];
