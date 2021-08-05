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
        'invalid_face_value' => 40,
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
];
