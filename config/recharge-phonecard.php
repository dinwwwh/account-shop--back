<?php

return [

    /**
     * Include status of recharge-phonecard
     *
     */
    'statuses' => [
        'pending' => 0,
        'approving' => 10,
        'success' => 20,
        'error' => 30,
    ],

    /**
     * Include ports of recharge-phonecard
     *
     */
    'ports' => [
        'manual' => 0,
        'thesieure' => 1,
        'naptudong' => 2,
    ],


    /**
     * Include invalid face values
     *
     */
    'face-values' => [10000, 20000, 30000, 50000, 100000, 200000, 300000, 500000, 1000000, 2000000, 5000000, 10000000],
];
