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
     * Config for manual port where user can recharge phonecards
     * and approve manually
     *
     */
    'port_manual_enable' => true,
    'manual_telcos' => [
        [
            'key' => 'VIETTEL',
            'name' => 'viettel',
            'faceValues' => [
                [
                    'value' => 10000,
                    'tax' => 20, #20%
                    'taxForInvalidFaceValue' => 100, #100%
                ],
                [
                    'value' => 20000,
                    'tax' => 30,
                    'taxForInvalidFaceValue' => 90,
                ],
                [
                    'value' => 50000,
                    'tax' => 40,
                    'taxForInvalidFaceValue' => 80,
                ],
                [
                    'value' => 100000,
                    'tax' => 50,
                    'taxForInvalidFaceValue' => 70,
                ]
            ]
        ],
    ],

    /**
     * Config for Thesieure port where user can recharge phonecards
     *
     */
    'port_manual_enable' => true,
    // 'manual_telcos' => , please get it access `thesieure.get-telcos` route
    'tsr_parent_id' => '4084020951',
    'tsr_parent_key' => 'f9a404988aeef59defecf848ff4c676c',

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
